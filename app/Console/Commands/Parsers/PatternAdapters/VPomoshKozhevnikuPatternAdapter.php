<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class VPomoshKozhevnikuPatternAdapter extends AbstractPatternAdapter
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

        $postImage = $xpath->query(expression: "//*[contains(@class, 'blog-post-image')]//img");
        $imageElements = $xpath->query(expression: "//*[contains(@class, 'wp-block-image')]//img");

        if ($postImage->length > 0) {
            /** @var DOMElement $element */
            $element = $postImage->item(0);

            $images[] = $element->getAttribute(qualifiedName: 'src');
        }

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute(qualifiedName: 'src');

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        $categoriesElements = $xpath->query(expression: "//*[contains(@class, 'ot-post-cats')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query(expression: "//*[contains(@class, 'mz-entry-tags')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query(expression: "//*[contains(@class, 'entry-title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $downloadLinkElements = $xpath->query(expression: "//*[contains(@class, 'blog-post-body')]//a[contains(text(), 'Скачать')]");

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

        $patternFilePath = null;
        $patternImagesPaths = [];

        try {
            $fileDownloadUrl = $downloadUrl;

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
                foreach ($videos as $video) {
                    $videosToCreate[] = $this->prepareVideoForCreation(
                        source: $video['source'],
                        videoId: $video['video_id'],
                    );
                }
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

                $this->info(
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
