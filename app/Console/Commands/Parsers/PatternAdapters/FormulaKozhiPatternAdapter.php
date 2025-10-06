<?php

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class FormulaKozhiPatternAdapter extends AbstractPatternAdapter
{
    public function processPattern(Pattern $pattern): void
    {
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
        $tags = [];

        $videos = $this->parseVideosFromString(
            content: $content,
            pattern: $pattern,
        );

        $imageElements = $xpath->query("//*[contains(@class, 'bam-single-post')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if (str_contains($imageUrl, 'lazy_placeholder')) {
                continue;
            }

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        $categoriesElements = $xpath->query("//*[contains(@class, 'cat-links')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query("//*[contains(@class, 'tags-links')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query("//*[contains(@class, 'entry-title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $downloadLinkElement = $xpath->query("//*[contains(@class, 'gde-text')]//a");

        if ($downloadLinkElement->length === 0) {
            $this->warn("No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong($pattern);

            return;
        }

        /** @var DOMElement $element */
        $element = $downloadLinkElement->item(0);
        $downloadUrl = $element->getAttribute('href');

        if (!$downloadUrl) {
            $this->warn("No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong($pattern);

            return;
        }

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

            if ($tags !== []) {
                $this->bindTags(
                    pattern: $pattern,
                    tags: $tags
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
