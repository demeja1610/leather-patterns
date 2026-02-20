<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use DOMXPath;
use Throwable;
use DOMElement;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;

class NeovimaPatternAdapter extends AbstractPatternAdapter
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
        $categories = [];
        $tags = [];

        $videos = $this->parseVideosFromString(
            content: $content,
            pattern: $pattern,
        );

        $reviews = $this->parseNeovimaPatternReviews(
            xpath: $xpath,
            pattern: $pattern,
        );

        $imageElements = $xpath->query(expression: "//*[contains(@class, 'woocommerce-product-gallery__wrapper')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute(qualifiedName: 'src');

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        $categoriesElements = $xpath->query(expression: "//*[contains(@class, 'posted_in')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query(expression: "//*[contains(@class, 'tagged_as')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query(expression: "//*[contains(@class, 'product_title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $addToCartButton = $xpath->query(expression: "//*[contains(@class, 'single_add_to_cart_button')]")->item(0);
        $addToCartUrl = $addToCartButton instanceof DOMElement ? $addToCartButton->getAttribute(qualifiedName: 'href') : null;

        if (!$addToCartUrl) {
            $this->warn(message: "No add to cart URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong(pattern: $pattern);

            return;
        }

        $downloadUrl = explode(separator: '?url=', string: $addToCartUrl);

        $patternFilePath = null;
        $patternImagesPaths = [];

        try {
            $fileDownloadUrl = $downloadUrl[1] ?? $downloadUrl[0];

            if (str_contains(haystack: $fileDownloadUrl, needle: 'youtu')) {
                $this->warn(message: "YouTube video detected, skipping file download...");

                $this->setDownloadUrlWrong(pattern: $pattern);

                return;
            }

            $patternFilePath = $this->downloadPatternFile(
                pattern: $pattern,
                url: $fileDownloadUrl,
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

            $videosToCreate = [];

            foreach ($videos as $video) {
                $videosToCreate[] = $this->prepareVideoForCreation(
                    source: $video['source'],
                    videoId: $video['video_id'],
                );
            }

            $reviewsToCreate = [];

            if ($reviews !== []) {
                $reviewsToCreate = $this->filterExistingReviews(
                    pattern: $pattern,
                    reviews: $reviews,
                );
            }

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

            if ($categories !== []) {
                $this->bindCategories(
                    pattern: $pattern,
                    categories: $categories,
                );
            }

            if ($tags !== []) {
                $this->bindTags(
                    pattern: $pattern,
                    tags: $tags,
                );
            }

            if ($videosToCreate !== []) {
                $videosToCreateCount = count(value: $videosToCreate);

                $this->success(
                    message: "Created {$videosToCreateCount} videos for pattern {$pattern->id}",
                );

                $pattern->videos()->saveMany(models: $videosToCreate);

                $this->setPatternVideoChecked(pattern: $pattern);
            }

            if ($reviewsToCreate !== []) {
                $reviewsToCreateCount = count(value: $reviewsToCreate);

                $this->success(
                    message: "Created {$reviewsToCreateCount} reviews for pattern {$pattern->id}",
                );

                $pattern->reviews()->saveMany(models: $reviewsToCreate);

                $this->setPatternReviewChecked(pattern: $pattern);
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

    /**
     * @return array<PatternReview>
     */
    protected function parseNeovimaPatternReviews(DOMXPath $xpath, Pattern $pattern): array
    {
        $this->info(message: 'Parsing reviews for pattern: ' . $pattern->id);

        $reviews = $xpath->query(expression: "//*[contains(@id, 'comments')]//*[contains(@class, 'comment-text')]");

        $toReturn = [];

        foreach ($reviews as $review) {
            $starsNodes = $xpath->query(expression: ".//strong[contains(@class, 'rating')]", contextNode: $review);
            $nameNodes = $xpath->query(expression: ".//*[contains(@class, 'woocommerce-review__author')]", contextNode: $review);
            $dateNodes = $xpath->query(expression: ".//*[contains(@class, 'woocommerce-review__published-date')]", contextNode: $review);
            $textNodes = $xpath->query(expression: ".//*[contains(@class, 'description')]", contextNode: $review);

            $stars = $starsNodes->item(0)?->textContent;

            if (!$stars) {
                $stars = null;
            }

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->attributes->getNamedItem('datetime')?->nodeValue;
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
