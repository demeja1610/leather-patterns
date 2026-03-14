<?php

declare(strict_types=1);

namespace App\Parsers\Pattern;

use Throwable;
use DOMElement;
use App\Models\Pattern;
use App\Parsers\PatternParser;
use App\Dto\Parser\Pattern\TagDto;
use App\Dto\Parser\Pattern\FileDto;
use Illuminate\Support\Facades\Log;
use App\Dto\Parser\Pattern\ImageDto;
use App\Dto\Parser\Pattern\TagListDto;
use App\Dto\Parser\Pattern\FileListDto;
use App\Dto\Parser\Pattern\ImageListDto;
use App\Dto\Parser\Pattern\ReviewListDto;
use App\Dto\Parser\Pattern\CategoryListDto;
use App\Dto\Parser\Pattern\ParsedPatternDto;
use App\Interfaces\Parsers\PatternParserInterface;
use App\Jobs\Parser\Pattern\UpdatePatternFromParsedPatternJob;

class LeatherPatternsPatternParser extends PatternParser implements PatternParserInterface
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

        $images = [];

        $imageElements = $xpath->query(expression: "//*[contains(@class, 'entry-content')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrls = $imageElement->getAttribute('srcset');
            $imageSrc = $imageElement->getAttribute('src');

            $image = $this->getParserService()->getBiggestImageFromSrcset(srcset: $imageUrls);

            if ($image !== null) {
                $images[] = $image;
            }

            if ($imageSrc !== null && $imageSrc !== '') {
                $images[] = new ImageDto(
                    url: $imageSrc,
                );
            }
        }

        $images = new ImageListDto(...array_map(
            array: array_unique(array_map(
                array: $images,
                callback: fn(ImageDto $image) => $image->getUrl()
            )),
            callback: fn(string $url) => new ImageDto(
                url: $url,
            )
        ));

        if ($images->isEmpty() === false) {
            $this->logFoundImages($images, $pattern);
        }

        $this->logSearchForTags($pattern);

        $tags = [];

        $tagsElements = $xpath->query(expression: "//*[contains(@class, 'entry-tags')]//a");

        /** @var \DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = new TagDto(
                name: $tagElement->textContent,
            );
        }

        $tags = new TagListDto(...$tags);

        if ($tags->isEmpty() === false) {
            $this->logFoundTags($tags, $pattern);
        }

        $this->logSearchForTitle($pattern);

        $title = $xpath->query(expression: "//*[contains(@class, 'entry-title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $this->logTitle($title, $pattern);

        $this->logSearchForFiles($pattern);

        $downloadLinks = $xpath->query(expression: "//*[contains(@class, 'download-link')]");
        $files = [];

        if ($downloadLinks->length > 0) {
            /** @var \DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $files[] = $downloadLink->getAttribute('href');
            }
        }

        if ($downloadLinks->length === 0) {
            $downloadLinks = $xpath->query(expression: "//*[contains(@class, 'check')]//a");

            /** @var \DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $files[] = $downloadLink->getAttribute('href');
            }
        }


        if ($downloadLinks->length === 0) {
            $downloadLinks = $xpath->query(expression: "//*[contains(@class, 'js-link')]");

            /** @var \DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $files[] = $this->decodeDataHref($downloadLink->getAttribute('data-href'));
            }
        }

        $files = array_filter(
            array: $files,
            callback: fn(string $url) => !str_contains($url, 'youtu')
        );

        $files = new FileListDto(...array_map(
            array: $files,
            callback: fn(string $url) => new FileDto(
                url: $url,
            ),
        ));

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
