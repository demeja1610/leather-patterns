<?php

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class AbzalaPatternAdapter extends AbstractPatternAdapter
{
    public function processPattern(Pattern $pattern): void
    {
        $baseURL = parse_url($pattern->source_url, PHP_URL_SCHEME) . '://' . parse_url($pattern->source_url, PHP_URL_HOST);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Throwable $th) {
            $this->error("Failed to parse pattern {$pattern->id}: " . $th->getMessage());

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

        $imageElements = $xpath->query("//*[contains(@class, 'com-content-article__body')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if ($imageUrl) {
                $images[] = $baseURL . '/' . trim($imageUrl, '/');
            }
        }

        $categoriesElements = $xpath->query("//*[contains(@class, 'category-name')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $title = $xpath->query("//*[contains(@class, 'page-header')]//h1")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $downloadLinkElements = [];

        $downloadLinkElements[] = $xpath->query("//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'Скачать')]");
        $downloadLinkElements[] = $xpath->query("//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'скачать')]");
        $downloadLinkElements[] = $xpath->query("//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'СКАЧАТЬ')]");
        $downloadLinkElements[] = $xpath->query("//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'СКАЧАТЬ')]/parent::a");
        $downloadLinkElements[] = $xpath->query("//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'скачать')]/parent::a");
        $downloadLinkElements[] = $xpath->query("//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'Скачать')]/parent::a");

        foreach ($downloadLinkElements as $downloadLinkElement) {
            if ($downloadLinkElement->length > 0) {
                $downloadLinkElements = $downloadLinkElement;

                break;
            }

            if (is_array($downloadLinkElements)) {
                $downloadLinkElements = $downloadLinkElements[0];
            }
        }

        if ($downloadLinkElements->length === 0) {
            $this->warn("No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong($pattern);

            return;
        }

        /** @var DOMElement $element */
        $element = $downloadLinkElements->item(0);
        $downloadUrl = $element->getAttribute('href');
        $downloadUrl = $baseURL . '/' . trim($downloadUrl, '/');

        $patternFilePath = null;
        $patternImagesPaths = [];

        try {
            $fileDownloadUrl = $downloadUrl;

            if (str_contains($fileDownloadUrl, 'youtu')) {
                $this->warn("YouTube video detected, skipping file download...");

                $this->setDownloadUrlWrong($pattern);

                return;
            }

            $patternFilePath = $this->downloadPatternFile(
                pattern: $pattern,
                url: $fileDownloadUrl,
            );

            if ($patternFilePath === null) {
                $this->error("Failed to download pattern file for pattern {$pattern->id}, skipping...");

                $this->setDownloadUrlWrong($pattern);

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

            Pattern::query()->where('id', $pattern->id)->update([
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
                $videosToCreateCount = count($videosToCreate);

                $this->success("Created $videosToCreateCount videos for pattern {$pattern->id}");

                $pattern->videos()->saveMany($videosToCreate);

                $this->setPatternVideoChecked($pattern);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error("Failed to download pattern file for pattern {$pattern->id}: {$e->getMessage()}");

            $this->error('Reverting changes, deleting downloaded files if they exist...');

            if ($patternFilePath !== null) {
                $this->deleteFileIfExists($patternFilePath);
            }

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists($patternImagesPaths);
            }
        }
    }
}
