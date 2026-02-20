<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class LaserbizPatternAdapter extends AbstractPatternAdapter
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
        $tags = [];

        $imageElements = $xpath->query(expression: "//*[contains(@class, 'full-foto')]//a");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute(qualifiedName: 'href');

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        $tagsElements = $xpath->query(expression: "//*[contains(@class, 'finfo')]//*[contains(@class, 'tags')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query(expression: "//*[contains(@class, 'fdl-title')]//span")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $downloadLink = $xpath->query(expression: "//*[contains(@id, 'dwm-link')]")->item(0);
        $downloadUrl = $downloadLink instanceof DOMElement ? $downloadLink->getAttribute(qualifiedName: 'href') : null;

        if (!$downloadUrl) {
            $this->warn(message: "No add to cart URL found for pattern {$pattern->id}, skipping...");

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
                extraHeaders: [
                    'Referer' => $pattern->source_url,
                ],
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

            Pattern::query()->where('id', $pattern->id)->update(values: [
                'title' => $title,
            ]);

            $this->changePatternMeta(
                pattern: $pattern,
            );

            if ($tags !== []) {
                $this->bindTags(
                    pattern: $pattern,
                    tags: $tags,
                );
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
