<?php

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class SkinpatPatternAdapter extends AbstractPatternAdapter
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

        $imageElements1 = $xpath->query("//*[contains(@class, 'entry-featured-img-wrap')]//img");
        $imageElements2 = $xpath->query("//div[contains(@class, 'entry-content')]//img[contains(@decoding, 'async')]");

        /** @var DOMElement $imageElement */
        foreach ($imageElements1 as $imageElement) {
            $imageUrls = $imageElement->getAttribute('data-srcset');

            $imageUrl = $this->getImageUrlFromSrcset($imageUrls);

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        /** @var DOMElement $imageElement */
        foreach ($imageElements2 as $imageElement) {
            $imageUrls = $imageElement->getAttribute('data-srcset');

            $imageUrl = $this->getImageUrlFromSrcset($imageUrls);

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        $categoriesElements = $xpath->query("//*[contains(@class, 'entry-byline-cats')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query("//*[contains(@class, 'entry-byline-tags')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query("//h1[contains(@class, 'entry-title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $title = trim($title);

        $rawDownloadLinkElements = [];
        $downloadLinkElements = [];

        $rawDownloadLinkElements[] = $xpath->query("//div[contains(@class, 'note')]//strong[contains(text(), 'Скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query("//div[contains(@class, 'note')]//b[contains(text(), 'Скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query("//div[contains(@class, 'note')]//strong[contains(text(), 'СКАЧАТЬ')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query("//div[contains(@class, 'note')]//b[contains(text(), 'СКАЧАТЬ')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query("//div[contains(@class, 'note')]//strong[contains(text(), 'скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query("//div[contains(@class, 'note')]//b[contains(text(), 'скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query("//p//b[contains(text(), 'Скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query("//p//strong[contains(text(), 'Скачать')]/parent::*//a");

        foreach ($rawDownloadLinkElements as $rawDownloadLinkElement) {
            if ($rawDownloadLinkElement->length > 0) {
                foreach ($rawDownloadLinkElement as $downloadLinkElement) {
                    $downloadLinkElements[] = $downloadLinkElement;
                }
            }
        }

        if ($downloadLinkElements === []) {
            $this->warn("No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong($pattern);

            return;
        }

        $downloadUrls = [];

        /** @var DOMElement $element */
        foreach ($downloadLinkElements as $element) {
            $downloadUrls[] = $element->getAttribute('href');
        }

        $downloadUrls = array_unique($downloadUrls);

        $patternFilePaths = [];
        $patternImagesPaths = [];

        try {
            $fileDownloadUrls = $downloadUrls;

            foreach ($fileDownloadUrls as $fileDownloadUrl) {
                if (str_contains($fileDownloadUrl, 'youtu')) {
                    $this->warn("YouTube video detected, skipping file download...");

                    $this->setDownloadUrlWrong($pattern);

                    return;
                }

                $patternFilePaths[] = $this->downloadPatternFile(
                    pattern: $pattern,
                    url: $fileDownloadUrl,
                );
            }

            $patternFilePaths = array_filter($patternFilePaths);

            if ($patternFilePaths === []) {
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
                filePaths: $patternFilePaths
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

            if ($patternFilePaths !== []) {
                foreach ($patternFilePaths as $patternFilePath) {
                    $this->deleteFileIfExists($patternFilePath);
                }
            }

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists($patternImagesPaths);
            }
        }
    }
}
