<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class LeatherPatternsPatternAdapter extends AbstractPatternAdapter
{
    public function processPattern(Pattern $pattern): void
    {
        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Throwable $throwable) {
            $this->error(
                "Failed to parse pattern {$pattern->id}: " . $throwable->getMessage()
            );

            return;
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

        $images = [];
        $tags = [];

        $videos = $this->parseVideosFromString(
            content: $content,
            pattern: $pattern,
        );

        $imageElements = $xpath->query("//*[contains(@class, 'entry-content')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrls = $imageElement->getAttribute('srcset');

            $imageUrl = $this->getImageUrlFromSrcset($imageUrls);

            if ($imageUrl !== '' && $imageUrl !== '0') {
                $images[] = $imageUrl;
            }
        }

        $tagsElements = $xpath->query("//*[contains(@class, 'entry-tags')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query("//*[contains(@class, 'entry-title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $downloadLinks = $xpath->query("//*[contains(@class, 'download-link')]");
        $downloadUrls = [];

        if ($downloadLinks->length > 0) {
            /** @var DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $downloadUrls[] = $downloadLink->getAttribute('href');
            }
        }

        if ($downloadLinks->length === 0) {
            $downloadLinks = $xpath->query("//*[contains(@class, 'check')]//a");

            /** @var DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $downloadUrls[] = $downloadLink->getAttribute('href');
            }
        }

        if ($downloadLinks->length === 0) {
            $downloadLinks = $xpath->query("//*[contains(@class, 'js-link')]");

            /** @var DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $downloadUrls[] = $this->decodeDataHref($downloadLink->getAttribute('data-href'));
            }
        }

        if ($downloadLinks->length === 0) {
            $this->warn("No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong($pattern);

            return;
        }

        $patternFilePaths = [];
        $patternImagesPaths = [];

        try {
            $fileDownloadUrls = $downloadUrls;

            foreach ($fileDownloadUrls as $fileDownloadUrl) {
                if (str_contains((string) $fileDownloadUrl, 'youtu')) {
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

            if ($tags !== []) {
                $this->bindTags(
                    pattern: $pattern,
                    tags: $tags
                );
            }

            if ($videosToCreate !== []) {
                $videosToCreateCount = count($videosToCreate);

                $this->success(
                    "Created {$videosToCreateCount} videos for pattern {$pattern->id}"
                );

                $pattern->videos()->saveMany($videosToCreate);

                $this->setPatternVideoChecked($pattern);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $this->error(
                "Failed to download pattern file for pattern {$pattern->id}: {$exception->getMessage()}"
            );

            $this->error(
                'Reverting changes, deleting downloaded files if they exist...'
            );

            foreach ($patternFilePaths as $patternFilePath) {
                $this->deleteFileIfExists($patternFilePath);
            }

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists($patternImagesPaths);
            }
        }
    }

    public function decodeDataHref(string $dataHref): ?string
    {
        if (str_starts_with($dataHref, "http") || str_starts_with($dataHref, "viber")) {
            return $dataHref;
        }

        $decoded = base64_decode($dataHref, true);

        if ($decoded === false) {
            return null;
        }

        if (str_starts_with($decoded, "http")) {
            return $decoded;
        }

        return $dataHref;
    }
}
