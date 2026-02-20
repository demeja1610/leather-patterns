<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class PatternHubPatternAdapter extends AbstractPatternAdapter
{
    public function processPattern(Pattern $pattern): void
    {
        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Throwable $throwable) {
            $this->error(
                message: "Failed to parse pattern {$pattern->id}: " . $throwable->getMessage(),
            );

            return;
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

        $images = [];
        $categories = [];
        $tags = [];

        $videos = $this->parseVideosFromString(
            content: $content,
            pattern: $pattern,
        );

        $imageElements = $xpath->query(expression: "//*[contains(@class, 'wp-block-gallery')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrls = $imageElement->getAttribute(qualifiedName: 'srcset');

            $imageUrl = $this->getImageUrlFromSrcset(srcset: $imageUrls);

            if ($imageUrl !== '' && $imageUrl !== '0') {
                $images[] = $imageUrl;
            }
        }

        $categoriesElements = $xpath->query(expression: "//*[contains(@class, 'taxonomy-category')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query(expression: "//*[contains(@class, 'taxonomy-post_tag')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query(expression: "//*[contains(@class, 'wp-block-post-title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $downloadLinkElements = [];
        $downloadLinkElements[] = $xpath
            ->query(expression: "//*[contains(@class, 'wp-block-post-content')]//a[contains(text(), 'Скачать выкройку')]");
        $downloadLinkElements[] = $xpath
            ->query(expression: "//*[contains(@class, 'wp-block-post-content')]//strong[contains(text(), 'Скачать выкройку бесплатно')]/parent::a");
        $downloadLinkElements[] = $xpath
            ->query(expression: "//*[contains(@class, 'wp-block-post-content')]//mark[contains(text(), 'Скачать выкройку бесплатно')]/parent::strong/parent::a");

        foreach ($downloadLinkElements as $_downloadLinkElement) {
            if ($_downloadLinkElement->length > 0) {
                $downloadLinkElements = $_downloadLinkElement;

                break;
            }

            if (is_array(value: $downloadLinkElements)) {
                $downloadLinkElements = $downloadLinkElements[0];
            }
        }

        if ($downloadLinkElements->length === 0) {
            $this->warn(message: "No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong(pattern: $pattern);

            return;
        }

        /** @var DOMElement $element */
        $element = $downloadLinkElements->item(0);
        $downloadUrl = $element->getAttribute(qualifiedName: 'href');

        if (!$downloadUrl) {
            $this->warn(message: "No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong(pattern: $pattern);

            return;
        }

        $downloadUrl = explode(separator: '?url=', string: $downloadUrl);

        $patternFilePath = null;
        $patternImagesPaths = [];

        try {
            $fileDownloadUrl = $downloadUrl[1] ?? $downloadUrl[0];

            if (str_contains(haystack: $fileDownloadUrl, needle: 'youtu')) {
                $this->warn(message: "YouTube video detected, skipping file download...");

                $this->setDownloadUrlWrong(pattern: $pattern);

                return;
            }

            $patternFilePath = $this->downloadPatternFile(
                pattern: $pattern,
                url: $fileDownloadUrl,
            );

            if ($patternFilePath === null) {
                $this->error(message: "Failed to download pattern file for pattern {$pattern->id}, skipping...");

                $this->setDownloadUrlWrong(pattern: $pattern);

                return;
            }

            $patternImagesPaths = $this->downloadPatternImages(
                pattern: $pattern,
                imageUrls: $images,
            );

            $videosToCreate = [];

            foreach ($videos as $video) {
                $videosToCreate[] = $this->prepareVideoForCreation(
                    source: $video['source'],
                    videoId: $video['video_id'],
                );
            }

            DB::beginTransaction();

            $this->bindFiles(
                pattern: $pattern,
                filePaths: [
                    $patternFilePath,
                ],
            );

            if ($patternImagesPaths !== []) {
                $this->bindImages(
                    pattern: $pattern,
                    imagePaths: $patternImagesPaths,
                );
            }

            Pattern::query()->where(column: 'id', operator: $pattern->id)->update(values: [
                'title' => $title,
            ]);

            $this->changePatternMeta(
                pattern: $pattern,
            );

            if ($categories !== []) {
                $this->bindCategories(
                    pattern: $pattern,
                    categories: $categories,
                );
            }

            if ($tags !== []) {
                $this->bindTags(
                    pattern: $pattern,
                    tags: $tags,
                );
            }

            if ($videosToCreate !== []) {
                $videosToCreateCount = count(value: $videosToCreate);

                $this->success(
                    message: "Created {$videosToCreateCount} videos for pattern {$pattern->id}",
                );

                $pattern->videos()->saveMany(models: $videosToCreate);

                $this->setPatternVideoChecked(pattern: $pattern);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $this->error(
                message: "Failed to download pattern file for pattern {$pattern->id}: {$exception->getMessage()}",
            );

            $this->error(message: 'Reverting changes, deleting downloaded files if they exist...');

            $this->deleteFileIfExists(filePath: $patternFilePath);

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists(imagePaths: $patternImagesPaths);
            }
        }
    }
}
