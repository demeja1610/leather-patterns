<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use DOMXPath;
use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class FirstKozhaPatternAdapter extends AbstractPatternAdapter
{
    public function processPattern(Pattern $pattern): void
    {
        $baseURL = parse_url(url: $pattern->source_url, component: PHP_URL_SCHEME) . '://' . parse_url(url: $pattern->source_url, component: PHP_URL_HOST);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Throwable $throwable) {
            $this->error(
                message: "Failed to parse pattern {$pattern->id}: " . $throwable->getMessage()
            );

            return;
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

        $images = [];
        $categories = [];
        $tags = [];

        $videos = $this->parseVideosFromString(
            content: $content,
            pattern: $pattern,
        );

        $reviews = $this->parse1KozhaPatternReviews(
            xpath: $xpath,
            pattern: $pattern
        );

        $imageElements1 = $xpath->query(expression: "//*[contains(@itemprop, 'articleBody')]//img");
        $imageElements2 = $xpath->query(expression: "//*[contains(@class, 'full-image')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements1 as $imageElement) {
            $imageUrl = $imageElement->getAttribute(qualifiedName: 'src');

            if ($imageUrl && !str_contains(haystack: $imageUrl, needle: '.gif')) {
                $images[] = trim(string: $baseURL, characters: '') . '/' . trim(string: $imageUrl, characters: '/');
            }
        }

        /** @var DOMElement $imageElement */
        foreach ($imageElements2 as $imageElement) {
            $imageUrl = $imageElement->getAttribute(qualifiedName: 'src');

            if ($imageUrl && !str_contains(haystack: $imageUrl, needle: '.gif')) {
                $images[] = trim(string: $baseURL, characters: '') . '/' . trim(string: $imageUrl, characters: '/');
            }
        }

        $categoriesElements = $xpath->query(expression: "//*[contains(@class, 'category-name')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query(expression: "//*[contains(@class, 'tags')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query(expression: "//h1[contains(@itemprop, 'name')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $title = trim(string: $title);

        $downloadLinks = $xpath->query(expression: "//*[contains(@class, 'at_url')]");
        $downloadUrls = [];

        if ($downloadLinks->length > 0) {
            /** @var DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $downloadUrls[] = $downloadLink->getAttribute(qualifiedName: 'href');
            }
        }

        if ($downloadLinks->length === 0) {
            $this->warn(message: "No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong(pattern: $pattern);

            return;
        }

        $patternFilePaths = [];
        $patternImagesPaths = [];

        try {
            $fileDownloadUrls = $downloadUrls;

            foreach ($fileDownloadUrls as $fileDownloadUrl) {
                if (str_contains(haystack: $fileDownloadUrl, needle: 'youtu')) {
                    $this->warn(message: "YouTube video detected, skipping file download...");

                    $this->setDownloadUrlWrong(pattern: $pattern);

                    return;
                }

                $patternFilePaths[] = $this->downloadPatternFile(
                    pattern: $pattern,
                    url: $fileDownloadUrl,
                );
            }

            $patternFilePaths = array_filter(array: $patternFilePaths);

            if ($patternFilePaths === []) {
                $this->error(message: "Failed to download pattern file for pattern {$pattern->id}, skipping...");

                $this->setDownloadUrlWrong(pattern: $pattern);

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

            $reviewsToCreate = [];

            if ($reviews !== []) {
                $reviewsToCreate = $this->filterExistingReviews(
                    pattern: $pattern,
                    reviews: $reviews
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

            Pattern::query()->where(column: 'id', operator: $pattern->id)->update(values: [
                'title' => $title,
            ]);

            $this->changePatternMeta(
                pattern: $pattern,
            );

            if ($categories !== []) {
                $this->bindCategories(
                    pattern: $pattern,
                    categories: $categories
                );
            }

            if ($tags !== []) {
                $this->bindTags(
                    pattern: $pattern,
                    tags: $tags
                );
            }

            if ($videosToCreate !== []) {
                $videosToCreateCount = count(value: $videosToCreate);

                $this->success(
                    message: "Created {$videosToCreateCount} videos for pattern {$pattern->id}"
                );

                $pattern->videos()->saveMany(models: $videosToCreate);

                $this->setPatternVideoChecked(pattern: $pattern);
            }

            if ($reviewsToCreate !== []) {
                $reviewsToCreateCount = count(value: $reviewsToCreate);

                $this->success(
                    message: "Created {$reviewsToCreateCount} reviews for pattern {$pattern->id}"
                );

                $pattern->reviews()->saveMany(models: $reviewsToCreate);

                $this->setPatternReviewChecked(pattern: $pattern);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $this->error(
                message: "Failed to download pattern file for pattern {$pattern->id}: {$exception->getMessage()}"
            );

            $this->error(message: 'Reverting changes, deleting downloaded files if they exist...');

            foreach ($patternFilePaths as $patternFilePath) {
                $this->deleteFileIfExists(filePath: $patternFilePath);
            }

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists(imagePaths: $patternImagesPaths);
            }
        }
    }

    /**
     * @return array<PatternReview>
     */
    protected function parse1KozhaPatternReviews(DOMXPath $xpath, Pattern $pattern): array
    {
        $this->info(message: 'Parsing reviews for pattern: ' . $pattern->id);

        $reviews = $xpath->query(expression: "//*[contains(@id, 'jcm-comments')]//*[contains(@class, 'jcm-block')]");

        $toReturn = [];

        foreach ($reviews as $review) {
            $nameNodes = $xpath->query(expression: ".//*[contains(@class, 'jcm-user-cm')]//span", contextNode: $review);
            $dateNodes = $xpath->query(expression: ".//*[contains(@class, 'jcm-post-header')]//meta", contextNode: $review);
            $textNodes = $xpath->query(expression: ".//*[contains(@class, 'jcm-post-body')]", contextNode: $review);

            $stars = null;

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->attributes->getNamedItem('content')?->nodeValue;
            $text = $textNodes->item(0)?->textContent;

            $toReturn[] = $this->prepareReviewForCreation(
                comment: trim(string: (string) $text),
                rating: floatval(value: $stars),
                reviewerName: trim(string: (string) $name),
                reviewedAt: trim(string: (string) $date),
            );
        }

        return $toReturn;
    }
}
