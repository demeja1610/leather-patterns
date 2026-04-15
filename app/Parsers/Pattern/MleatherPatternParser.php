<?php

declare(strict_types=1);

namespace App\Parsers\Pattern;

use DOMXPath;
use Throwable;
use DOMElement;
use Carbon\Carbon;
use App\Models\Pattern;
use App\Parsers\PatternParser;
use App\Dto\Parser\Pattern\FileDto;
use App\Dto\Parser\Pattern\ImageDto;
use App\Dto\Parser\Pattern\ReviewDto;
use App\Dto\Parser\Pattern\TagListDto;
use App\Dto\Parser\Pattern\FileListDto;
use App\Dto\Parser\Pattern\ImageListDto;
use App\Dto\Parser\Pattern\ReviewListDto;
use App\Dto\Parser\Pattern\CategoryListDto;
use App\Dto\Parser\Pattern\ParsedPatternDto;
use App\Interfaces\Parsers\PatternParserInterface;
use App\Jobs\Parser\Pattern\UpdatePatternFromParsedPatternJob;

class MleatherPatternParser extends PatternParser implements PatternParserInterface
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

        $this->logSearchForTitle($pattern);

        $title = $this->getTitle($xpath);

        $this->logTitle($title, $pattern);

        $this->logSearchForFiles($pattern);

        $files = $this->getFiles($xpath);

        $this->logFoundFiles($files, $pattern);

        $this->logSearchForReviews($pattern);

        $reviews = $this->parseReviews($xpath);

        $updatePattern = new ParsedPatternDto(
            pattern: $pattern,
            title: $title,
            categories: new CategoryListDto(),
            tags: new TagListDto(),
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

        $imageElements = $xpath->query("//*[contains(@class, 'article-content')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageSrc = $imageElement->getAttribute('src');

            if ($imageSrc !== null && $imageSrc !== '') {
                $images[] = new ImageDto(
                    url: trim($imageSrc, '/'),
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

    protected function getTitle(DOMXPath &$xpath): string
    {
        $title = $xpath->query("//h1[contains(@class, 'heading')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        return $title;
    }

    protected function getFiles(DOMXPath &$xpath): FileListDto
    {
        $files = [];
        $downloadLinkElements = [];

        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'article-content')]//a[contains(text(), 'Скачать')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'article-content')]//a[contains(text(), 'скачать')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'article-content')]//a[contains(text(), 'СКАЧАТЬ')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'article-content')]//strong[contains(text(), 'СКАЧАТЬ')]/parent::a");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'article-content')]//strong[contains(text(), 'скачать')]/parent::a");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'article-content')]//strong[contains(text(), 'Скачать')]/parent::a");

        foreach ($downloadLinkElements as $downloadLinkElement) {
            if ($downloadLinkElement->length > 0) {
                $downloadLinkElements = $downloadLinkElement;

                break;
            }

            if (is_array(value: $downloadLinkElements)) {
                $downloadLinkElements = $downloadLinkElements[0];
            }
        }

        if ($downloadLinkElements->length > 0) {
            /** @var DOMElement $downloadLink */
            foreach ($downloadLinkElements as $downloadLink) {
                $downloadUrl = $downloadLink->getAttribute('href');
                $downloadUrl = trim(string: $downloadUrl, characters: '/');

                $files[] = $downloadUrl;
            }
        }

        $files = array_unique(array_filter(
            array: $files,
            callback: fn(string $file) => !str_contains($file, 'youtu')
        ));

        return new FileListDto(...array_map(
            array: $files,
            callback: fn(string $file) => new FileDto(
                url: $file,
            ),
        ));
    }

    protected function parseReviews(DOMXPath &$xpath): ReviewListDto
    {
        $reviews = [];

        $reviewsEls = $xpath->query("//*[contains(@class, 'reviews')]//*[contains(@class, 'masonry-reviews-item')]");

        foreach ($reviewsEls as $reviewsEl) {
            $nameNodes = $xpath->query(".//*[contains(@class, 'author')]", $reviewsEl);
            $dateNodes = $xpath->query(".//*[contains(@class, 'date", $reviewsEl);
            $textNodes = $xpath->query(".//*[contains(@class, 'review-content')]", $reviewsEl);
            $stars = null;

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->textContent;
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
}
