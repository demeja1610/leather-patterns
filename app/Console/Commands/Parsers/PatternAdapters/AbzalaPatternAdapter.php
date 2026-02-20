<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class AbzalaPatternAdapter extends AbstractPatternAdapter
{
    public function processPattern(Pattern $pattern): void
    {
        $baseURL = parse_url(url: $pattern->source_url, component: PHP_URL_SCHEME) . '://' . parse_url(url: $pattern->source_url, component: PHP_URL_HOST);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Throwable $throwable) {
            $this->error(
                message: "Failed to parse pattern {$pattern->id}: " . $throwable->getMessage()
            );

            return;
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

        $images = [];
        $categories = [];

        $videos = $this->parseVideosFromString(
            content: $content,
            pattern: $pattern,
        );

        $imageElements = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute(qualifiedName: 'src');

            if ($imageUrl) {
                $images[] = $baseURL . '/' . trim(string: $imageUrl, characters: '/');
            }
        }

        $categoriesElements = $xpath->query(expression: "//*[contains(@class, 'category-name')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $title = $xpath->query(expression: "//*[contains(@class, 'page-header')]//h1")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $downloadLinkElements = [];

        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'Скачать')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'скачать')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'СКАЧАТЬ')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'СКАЧАТЬ')]/parent::a");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'скачать')]/parent::a");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'Скачать')]/parent::a");

        foreach ($downloadLinkElements as $downloadLinkElement) {
            if ($downloadLinkElement->length > 0) {
                $downloadLinkElements = $downloadLinkElement;

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
        $downloadUrl = $baseURL . '/' . trim(string: $downloadUrl, characters: '/');

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
                imageUrls: $images
            );

            $videosToCreate = [];

            foreach ($videos as $video) {
                $videosToCreate[] = $this->prepareVideoForCreation(
                    source: $video['source'],
                    videoId: $video['video_id']
                );
            }

            DB::beginTransaction();

            $this->bindFiles(
                pattern: $pattern,
                filePaths: [
                    $patternFilePath
                ]
            );

            if ($patternImagesPaths !== []) {
                $this->bindImages(
                    pattern: $pattern,
                    imagePaths: $patternImagesPaths
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
                    categories: $categories
                );
            }

            if ($videosToCreate !== []) {
                $videosToCreateCount = count(value: $videosToCreate);

                $this->success(message: "Created {$videosToCreateCount} videos for pattern {$pattern->id}");

                $pattern->videos()->saveMany(models: $videosToCreate);

                $this->setPatternVideoChecked(pattern: $pattern);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $this->error(
                message: "Failed to download pattern file for pattern {$pattern->id}: {$exception->getMessage()}"
            );

            $this->error(message: 'Reverting changes, deleting downloaded files if they exist...');

            $this->deleteFileIfExists(filePath: $patternFilePath);

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists(imagePaths: $patternImagesPaths);
            }
        }
    }
}
