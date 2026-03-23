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

class CutMePatternParser extends PatternParser implements PatternParserInterface
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

        $files = $this->getFiles($xpath, $pattern);

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
        $imageElements = $xpath->query("//*[contains(@class, 'woocommerce-product-gallery__wrapper')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageSrc = $imageElement->getAttribute('src');

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

        $reviewsEls = $xpath->query(expression: "//*[contains(@id, 'comments')]//*[contains(@class, 'comment-text')]");

        foreach ($reviewsEls as $reviewsEl) {
            $starsNodes = $xpath->query(expression: ".//strong[contains(@class, 'rating')]", contextNode: $reviewsEl);
            $nameNodes = $xpath->query(expression: ".//*[contains(@class, 'woocommerce-review__author')]", contextNode: $reviewsEl);
            $dateNodes = $xpath->query(expression: ".//*[contains(@class, 'woocommerce-review__published-date')]", contextNode: $reviewsEl);
            $textNodes = $xpath->query(expression: ".//*[contains(@class, 'description')]", contextNode: $reviewsEl);

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

    protected function getFiles(DOMXPath &$xpath, Pattern &$pattern): FileListDto
    {
        $form = $xpath->query("//*[contains(@class, 'somdn-download-form')]")->item(0);

        if (!$form) {
            $this->logNoDownloadLinksFound($pattern);
        } else {
            do {
                /** @var DOMElement $form */
                $downloadUrl = $form->getAttribute(qualifiedName: 'action');

                $downloadKeyEl = $xpath->query(expression: "//*[contains(@name, 'somdn_download_key')]")->item(0);
                $productIdEl = $xpath->query(expression: "//*[contains(@name, 'somdn_product')]")->item(0);

                if (!$productIdEl) {
                    $this->logNoProductIdFound($pattern);

                    break;
                }

                if (!$downloadKeyEl) {
                    $this->logNoDownloadKeyFound($pattern);

                    break;
                }

                /** @var DOMElement $downloadKeyEl */
                $downloadKey = $downloadKeyEl->getAttribute(qualifiedName: 'value');

                /** @var DOMElement $productIdEl */
                $productId = $productIdEl->getAttribute(qualifiedName: 'value');

                $action = 'somdn_download_single';

                $postParams = [
                    'somdn_download_key' => $downloadKey,
                    'action' => $action,
                    'somdn_product' => $productId,
                ];

                $files[] = new FileDto(
                    url: $downloadUrl,
                    postData: $postParams,
                );
            } while (0);
        }

        $files = array_filter(
            array: $files,
            callback: fn(FileDto $file) => !str_contains($file->getUrl(), 'youtu')
        );

        return new FileListDto(...$files);
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
