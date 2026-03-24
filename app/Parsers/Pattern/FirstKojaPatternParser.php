<?php

declare(strict_types=1);

namespace App\Parsers\Pattern;

use DOMXPath;
use Throwable;
use DOMElement;
use Carbon\Carbon;
use App\Models\Pattern;
use App\Parsers\PatternParser;
use App\Dto\Parser\Pattern\TagDto;
use App\Dto\Parser\Pattern\FileDto;
use App\Dto\Parser\Pattern\ImageDto;
use App\Dto\Parser\Pattern\ReviewDto;
use App\Dto\Parser\Pattern\TagListDto;
use App\Dto\Parser\Pattern\CategoryDto;
use App\Dto\Parser\Pattern\FileListDto;
use App\Dto\Parser\Pattern\ImageListDto;
use App\Dto\Parser\Pattern\ReviewListDto;
use App\Dto\Parser\Pattern\CategoryListDto;
use App\Dto\Parser\Pattern\ParsedPatternDto;
use App\Interfaces\Parsers\PatternParserInterface;
use App\Jobs\Parser\Pattern\UpdatePatternFromParsedPatternJob;

class FirstKojaPatternParser extends PatternParser implements PatternParserInterface
{
    protected string $baseUrl = '';

    public function processPattern(Pattern $pattern): void
    {
        $this->logParsePattern($pattern);

        $this->baseUrl = parse_url($pattern->source_url, PHP_URL_SCHEME) . '://' . parse_url($pattern->source_url, PHP_URL_HOST);

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

        $this->logSearchForCategories($pattern);

        $categories = $this->getCategories($xpath);

        if ($categories->isEmpty() === false) {
            $this->logFoundCategories($categories, $pattern);
        }

        $this->logSearchForTitle($pattern);

        $title = $this->getTitle($xpath);

        $this->logTitle($title, $pattern);

        $this->logSearchForFiles($pattern);

        $files = $this->getFiles($xpath);

        $this->logFoundFiles($files, $pattern);

        $this->logSearchForReviews($pattern);

        $reviews = $this->parseReviews($xpath);

        $this->logFoundReviews($reviews, $pattern);

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

        $imageElements = $xpath->query("//*[contains(@itemprop, 'articleBody')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if ($imageUrl && !str_contains(haystack: $imageUrl, needle: '.gif')) {
                $images[] = new ImageDto(
                    url: trim($this->baseUrl, '/') . '/' . trim($imageUrl, '/'),
                );
            }
        }

        $imageElements2 = $xpath->query("//*[contains(@class, 'full-image')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements2 as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if ($imageUrl && !str_contains(haystack: $imageUrl, needle: '.gif')) {
                $images[] = new ImageDto(
                    url: trim($this->baseUrl, '/') . '/' . trim($imageUrl, '/'),
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

        $tagsElements = $xpath->query("//*[contains(@class, 'tags')]//a");

        /** @var \DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = new TagDto(
                name: $tagElement->textContent,
            );
        }

        return new TagListDto(...$tags);
    }

    protected function getCategories(DOMXPath &$xpath): CategoryListDto
    {
        $categories = [];
        $categoriesElements = $xpath->query("//*[contains(@class, 'category-name')]//a");

        /** @var \DOMElement $categoriesElement */
        foreach ($categoriesElements as $categoriesElement) {
            $categories[] = new CategoryDto(
                name: $categoriesElement->textContent,
            );
        }

        return new CategoryListDto(...$categories);
    }

    protected function parseReviews(DOMXPath &$xpath): ReviewListDto
    {
        $reviews = [];

        $reviewsEls = $xpath->query("//*[contains(@id, 'jcm-comments')]//*[contains(@class, 'jcm-block')]");

        foreach ($reviewsEls as $reviewsEl) {
            $nameNodes = $xpath->query(".//*[contains(@class, 'jcm-user-cm')]//span", $reviewsEl);
            $dateNodes = $xpath->query(".//*[contains(@class, 'jcm-post-header')]//meta", $reviewsEl);
            $textNodes = $xpath->query(".//*[contains(@class, 'jcm-post-body')]", $reviewsEl);
            $stars = null;

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->attributes->getNamedItem('content')?->nodeValue;
            $text = $textNodes->item(0)?->textContent;

            $reviews[] = new ReviewDto(
                reviewerName: trim(string: (string) $name),
                reviewedAt: Carbon::parse($date),
                comment: trim(string: (string) $text),
                rating: floatval(value: $stars)
            );
        }

        return new ReviewListDto(
            ...$reviews
        );
    }

    protected function getTitle(DOMXPath &$xpath): string
    {
        $title = $xpath->query("//h1[contains(@itemprop, 'name')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        return $title;
    }

    protected function getFiles(DOMXPath &$xpath): FileListDto
    {
        $downloadLinks = $xpath->query("//*[contains(@class, 'at_url')]");
        $files = [];

        if ($downloadLinks->length > 0) {
            /** @var DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $files[] = $downloadLink->getAttribute('href');
            }
        }

        $files = array_filter(
            array: $files,
            callback: fn(string $file) => !str_contains($file, 'youtu')
        );

        return new FileListDto(...array_map(
            array: $files,
            callback: fn(string $file) => new FileDto(
                url: $file,
            ),
        ));
    }

    protected function logNoProductIdFound(Pattern &$pattern): void
    {
        $this->log('warn', "No product ID found for pattern with ID: {$pattern->id}");
    }

    protected function logNoDownloadKeyFound(Pattern &$pattern): void
    {
        $this->log('warn', "No download key found for pattern with ID: {$pattern->id}");
    }
}
