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

class SkinPatPatternParser extends PatternParser implements PatternParserInterface
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

        $updatePattern = new ParsedPatternDto(
            pattern: $pattern,
            title: $title,
            categories: $categories,
            tags: $tags,
            images: $images,
            files: $files,
            videos: $videos,
            reviews: new ReviewListDto(),
        );

        dispatch(new UpdatePatternFromParsedPatternJob($updatePattern));
    }

    protected function getImages(DOMXPath &$xpath): ImageListDto
    {
        $images = [];

        $imageElements = $xpath->query("//*[contains(@class, 'entry-featured-img-wrap')]//img");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrls = $imageElement->getAttribute('srcset');

            if ($imageUrls !== '' && $imageUrls !== null) {
                $image = $this->getParserService()->getBiggestImageFromSrcset(srcset: $imageUrls);

                if ($image !== null) {
                    $images[] = $image;
                }
            } else {
                $imageUrls = $imageElement->getAttribute('data-srcset');

                $image = $this->getParserService()->getBiggestImageFromSrcset(srcset: $imageUrls);

                if ($image !== null) {
                    $images[] = $image;
                }
            }
        }

        $imageElements2 = $xpath->query(expression: "//div[contains(@class, 'entry-content')]//img[contains(@decoding, 'async')]");

        /** @var \DOMElement $imageElement */
        foreach ($imageElements2 as $imageElement) {
            $sizes = $imageElement->getAttribute('sizes');

            if ($sizes === '' || $sizes === null) {
                continue;
            }

            $imageUrls = $imageElement->getAttribute('srcset');

            if ($imageUrls !== '' && $imageUrls !== null) {
                $image = $this->getParserService()->getBiggestImageFromSrcset(srcset: $imageUrls);

                if ($image !== null) {
                    $images[] = $image;
                }
            } else {
                $imageUrls = $imageElement->getAttribute('data-srcset');

                $image = $this->getParserService()->getBiggestImageFromSrcset(srcset: $imageUrls);

                if ($image !== null) {
                    $images[] = $image;
                }
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
        $tagsElements = $xpath->query("//*[contains(@class, 'entry-byline-tags')]//a");

        $tags = [];

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
        $categoriesElements = $xpath->query("//*[contains(@class, 'entry-byline-cats')]//a");

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
        $title = $xpath->query("//h1[contains(@class, 'entry-title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        return $title;
    }

    protected function getFiles(DOMXPath &$xpath): FileListDto
    {
        $files = [];

        $downloadLinkElements = [];

        $rawDownloadLinkElements = [];


        $rawDownloadLinkElements[] = $xpath->query(expression: "//div[contains(@class, 'note')]//strong[contains(text(), 'Скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query(expression: "//div[contains(@class, 'note')]//b[contains(text(), 'Скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query(expression: "//div[contains(@class, 'note')]//strong[contains(text(), 'СКАЧАТЬ')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query(expression: "//div[contains(@class, 'note')]//b[contains(text(), 'СКАЧАТЬ')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query(expression: "//div[contains(@class, 'note')]//strong[contains(text(), 'скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query(expression: "//div[contains(@class, 'note')]//b[contains(text(), 'скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query(expression: "//p//b[contains(text(), 'Скачать')]/parent::*//a");
        $rawDownloadLinkElements[] = $xpath->query(expression: "//p//strong[contains(text(), 'Скачать')]/parent::*//a");

        foreach ($rawDownloadLinkElements as $rawDownloadLinkElement) {
            if ($rawDownloadLinkElement->length > 0) {
                foreach ($rawDownloadLinkElement as $downloadLinkElement) {
                    $downloadLinkElements[] = $downloadLinkElement;
                }
            }
        }

        $downloadUrls = [];

        /** @var DOMElement $element */
        foreach ($downloadLinkElements as $element) {
            $downloadUrls[] = $element->getAttribute(qualifiedName: 'href');
        }

        $downloadUrls = array_unique($downloadUrls);

        $files = array_filter(
            array: $downloadUrls,
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
