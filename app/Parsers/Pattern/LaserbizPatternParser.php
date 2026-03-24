<?php

declare(strict_types=1);

namespace App\Parsers\Pattern;

use DOMXPath;
use Throwable;
use DOMElement;
use App\Models\Pattern;
use App\Parsers\PatternParser;
use App\Dto\Parser\Pattern\TagDto;
use App\Dto\Parser\Pattern\FileDto;
use App\Dto\Parser\Pattern\ImageDto;
use App\Dto\Parser\Pattern\TagListDto;
use App\Dto\Parser\Pattern\FileListDto;
use App\Dto\Parser\Pattern\ImageListDto;
use App\Dto\Parser\Pattern\ReviewListDto;
use App\Dto\Parser\Pattern\CategoryListDto;
use App\Dto\Parser\Pattern\ParsedPatternDto;
use App\Interfaces\Parsers\PatternParserInterface;
use App\Jobs\Parser\Pattern\UpdatePatternFromParsedPatternJob;

class LaserbizPatternParser extends PatternParser implements PatternParserInterface
{
    public function processPattern(Pattern $pattern): void
    {
        $this->logParsePattern($pattern);

        try {
            $content = $this->getParserService()->parseUrl($pattern->source_url);
        } catch (Throwable $th) {
            $this->logFailedToParsePattern($pattern, $th);

            return;
        }

        $dom = $this->getParserService()->parseDOM($content);
        $xpath = $this->getParserService()->getDOMXPath($dom);

        $this->logSearchForVideos($pattern);

        $videos = $this->getParserService()->getVideosFromString(
            content: $content,
        );

        if ($videos->isEmpty() === false) {
            $this->logFoundedVideos($videos, $pattern);
        }

        $this->logSearchForImages($pattern);

        $images = $this->getImages($xpath);

        if ($images->isEmpty() === false) {
            $this->logFoundImages($images, $pattern);
        }

        $this->logSearchForTags($pattern);

        $tags = $this->getTags($xpath);

        if ($tags->isEmpty() === false) {
            $this->logFoundTags($tags, $pattern);
        }

        $this->logSearchForTitle($pattern);

        $title = $this->getTitle($xpath);

        $this->logTitle($title, $pattern);

        $this->logSearchForFiles($pattern);

        $files = $this->getFiles($xpath, $pattern);

        $this->logFoundFiles($files, $pattern);

        $categories = new CategoryListDto();
        $reviews = new ReviewListDto();

        $updatePattern = new ParsedPatternDto(
            pattern: $pattern,
            title: $title,
            categories: $categories,
            tags: $tags,
            images: $images,
            files: $files,
            videos: $videos,
            reviews: $reviews,
        );

        dispatch(new UpdatePatternFromParsedPatternJob($updatePattern));
    }

    protected function getImages(DOMXPath &$xpath): ImageListDto
    {
        $images = [];

        $imageElements = $xpath->query(expression: "//*[contains(@class, 'full-foto')]//a");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageSrc = $imageElement->getAttribute('href');

            if ($imageSrc !== null && $imageSrc !== '') {
                $images[] = new ImageDto(
                    url: $imageSrc,
                );
            }
        }

        return new ImageListDto(...array_map(
            array: array_unique(array_map(
                array: $images,
                callback: fn(ImageDto $image) => $image->getUrl()
            )),
            callback: fn(string $url) => new ImageDto(
                url: $url,
            )
        ));
    }

    protected function getTags(DOMXPath &$xpath): TagListDto
    {
        $tags = [];

        $tagsElements = $xpath->query("//*[contains(@class, 'finfo')]//*[contains(@class, 'tags')]//a");

        /** @var \DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = new TagDto(
                name: $tagElement->textContent,
            );
        }

        return new TagListDto(...$tags);
    }

    protected function getTitle(DOMXPath &$xpath): string
    {
        $title = $xpath->query(expression: "//*[contains(@class, 'fdl-title')]//span")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        return $title;
    }

    protected function getFiles(DOMXPath &$xpath, Pattern &$pattern): FileListDto
    {
        $files = [];

        $downloadLink = $xpath->query(expression: "//*[contains(@id, 'dwm-link')]")->item(0);

        $downloadUrl = $downloadLink instanceof DOMElement ? $downloadLink->getAttribute(qualifiedName: 'href') : null;

        if ($downloadUrl !== null) {
            $files[] = $downloadUrl;
        }

        $files = array_filter(
            array: $files,
            callback: fn(string $url) => !str_contains($url, 'youtu')
        );

        return new FileListDto(...array_map(
            array: $files,
            callback: fn(string $url) => new FileDto(
                url: $url,
                extraHeaders: [
                    'Referer' => $pattern->source_url,
                ]
            ),
        ));
    }

    protected function decodeDataHref(string $dataHref): ?string
    {
        if (str_starts_with(haystack: $dataHref, needle: "http") || str_starts_with(haystack: $dataHref, needle: "viber")) {
            return $dataHref;
        }

        $decoded = base64_decode($dataHref, true);

        if ($decoded === false) {
            return null;
        }

        if (str_starts_with(haystack: $decoded, needle: "http")) {
            return $decoded;
        }

        return $dataHref;
    }
}
