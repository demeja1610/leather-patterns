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

class NeovimaPatternParser extends PatternParser implements PatternParserInterface
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

        $imageElements = $xpath->query("//*[contains(@class, 'woocommerce-product-gallery__wrapper')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        return new ImageListDto(...array_map(
            array: array_unique($images),
            callback: fn(string $url) => new ImageDto(
                url: $url,
            )
        ));
    }

    protected function getTags(DOMXPath &$xpath): TagListDto
    {
        $tags = [];

        $tagsElements = $xpath->query("//*[contains(@class, 'tagged_as')]//a");

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
        $categoriesElements = $xpath->query("//*[contains(@class, 'posted_in')]//a");

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
            $starsNodes = $xpath->query(".//strong[contains(@class, 'rating')]", $reviewsEl);
            $nameNodes = $xpath->query(".//*[contains(@class, 'woocommerce-review__author')]", $reviewsEl);
            $dateNodes = $xpath->query(".//*[contains(@class, 'woocommerce-review__published-date')]", $reviewsEl);
            $textNodes = $xpath->query(".//*[contains(@class, 'description')]", $reviewsEl);

            $stars = $starsNodes->item(0)?->textContent;

            if (!$stars) {
                $stars = null;
            }

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->attributes->getNamedItem('datetime')?->nodeValue;
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
        $title = $xpath->query("//*[contains(@class, 'product_title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        return $title;
    }

    protected function getFiles(DOMXPath &$xpath): FileListDto
    {
        $files = [];

        $addToCartButton = $xpath->query(expression: "//*[contains(@class, 'single_add_to_cart_button')]")->item(0);
        $addToCartUrl = $addToCartButton instanceof DOMElement ? $addToCartButton->getAttribute(qualifiedName: 'href') : null;

        if ($addToCartUrl !== null) {
            $downloadUrl = explode(separator: '?url=', string: $addToCartUrl);

            $files[] = $downloadUrl[1] ?? $downloadUrl[0];
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
