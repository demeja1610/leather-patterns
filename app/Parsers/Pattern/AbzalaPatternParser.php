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
use App\Dto\Parser\Pattern\CategoryDto;
use App\Dto\Parser\Pattern\FileListDto;
use App\Dto\Parser\Pattern\ImageListDto;
use App\Dto\Parser\Pattern\ReviewListDto;
use App\Dto\Parser\Pattern\CategoryListDto;
use App\Dto\Parser\Pattern\ParsedPatternDto;
use App\Interfaces\Parsers\PatternParserInterface;
use App\Jobs\Parser\Pattern\UpdatePatternFromParsedPatternJob;

class AbzalaPatternParser extends PatternParser implements PatternParserInterface
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

        $baseUrl = parse_url($pattern->source_url, PHP_URL_SCHEME) . '://' . parse_url($pattern->source_url, PHP_URL_HOST);

        $this->logSearchForVideos($pattern);

        $videos = $this->getParserService()->getVideosFromString(
            content: $content,
        );

        if ($videos->isEmpty() === false) {
            $this->logFoundedVideos($videos, $pattern);
        }

        $this->logSearchForImages($pattern);

        $images = $this->getImages($xpath, $baseUrl);

        if ($images->isEmpty() === false) {
            $this->logFoundImages($images, $pattern);
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

        $files = $this->getFiles($xpath, $baseUrl);

        $this->logFoundFiles($files, $pattern);

        $updatePattern = new ParsedPatternDto(
            pattern: $pattern,
            title: $title,
            categories: $categories,
            tags: new TagListDto(),
            images: $images,
            files: $files,
            videos: $videos,
            reviews: new ReviewListDto(),
        );

        dispatch(new UpdatePatternFromParsedPatternJob($updatePattern));
    }

    protected function getImages(DOMXPath &$xpath, string &$baseUrl): ImageListDto
    {
        $images = [];

        $imageElements = $xpath->query("//*[contains(@class, 'com-content-article__body')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageSrc = $imageElement->getAttribute('src');

            if ($imageSrc !== null && $imageSrc !== '') {
                $images[] = new ImageDto(
                    url: $baseUrl . '/' . trim($imageSrc, '/'),
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

    protected function getCategories(DOMXPath &$xpath): CategoryListDto
    {
        $categoriesElements = $xpath->query("//*[contains(@class, 'category-name')]//a");

        /** @var \DOMElement $categoriesElement */
        foreach ($categoriesElements as $categoriesElement) {
            $categories[] = new CategoryDto(
                name: $categoriesElement->textContent,
            );
        }

        return new CategoryListDto(...$categories);
    }

    protected function getTitle(DOMXPath &$xpath): string
    {
        $title = $xpath->query("//*[contains(@class, 'page-header')]//h1")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        return $title;
    }

    protected function getFiles(DOMXPath &$xpath, string &$baseUrl): FileListDto
    {
        $files = [];
        $downloadLinkElements = [];

        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'Скачать')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'скачать')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//a[contains(text(), 'СКАЧАТЬ')]");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'СКАЧАТЬ')]/parent::a");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'скачать')]/parent::a");
        $downloadLinkElements[] = $xpath->query(expression: "//*[contains(@class, 'com-content-article__body')]//strong[contains(text(), 'Скачать')]/parent::a");

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
                $downloadUrl = $baseUrl . '/' . trim(string: $downloadUrl, characters: '/');

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

    protected function logNoProductIdFound(Pattern &$pattern): void
    {
        $this->log('warn', "No product ID found for pattern with ID: {$pattern->id}");
    }

    protected function logNoDownloadKeyFound(Pattern &$pattern): void
    {
        $this->log('warn', "No download key found for pattern with ID: {$pattern->id}");
    }
}
