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
                "Failed to parse pattern {$pattern->id}: " . $throwable->getMessage()
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
            pattern: $pattern
        );

        $imageElements = $xpath->query("//*[contains(@class, 'woocommerce-product-gallery__wrapper')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if ($imageUrl) {
                $images[] = $imageUrl;
            }
        }

        $categoriesElements = $xpath->query("//*[contains(@class, 'posted_in')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query("//*[contains(@class, 'tagged_as')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query("//*[contains(@class, 'product_title')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $addToCartButton = $xpath->query("//*[contains(@class, 'single_add_to_cart_button')]")->item(0);
        $addToCartUrl = $addToCartButton instanceof DOMElement ? $addToCartButton->getAttribute('href') : null;

        if (!$addToCartUrl) {
            $this->warn("No add to cart URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong($pattern);

            return;
        }

        $downloadUrl = explode('?url=', $addToCartUrl);

        $patternFilePath = null;
        $patternImagesPaths = [];

        try {
            $fileDownloadUrl = $downloadUrl[1] ?? $downloadUrl[0];

            if (str_contains($fileDownloadUrl, 'youtu')) {
                $this->warn("YouTube video detected, skipping file download...");

                $this->setDownloadUrlWrong($pattern);

                return;
            }

            $patternFilePath = $this->downloadPatternFile(
                pattern: $pattern,
                url: $fileDownloadUrl,
            );

            if ($patternFilePath === null) {
                $this->error("Failed to download pattern file for pattern {$pattern->id}, skipping...");

                $this->setDownloadUrlWrong($pattern);

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
                filePaths: [
                    $patternFilePath
                ]
            );

            if ($patternImagesPaths !== []) {
                $this->bindImages(
                    pattern: $pattern,
                    imagePaths: $patternImagesPaths
                );
            }

            Pattern::query()->where('id', $pattern->id)->update([
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
                $videosToCreateCount = count($videosToCreate);

                $this->success(
                    "Created {$videosToCreateCount} videos for pattern {$pattern->id}"
                );

                $pattern->videos()->saveMany($videosToCreate);

                $this->setPatternVideoChecked($pattern);
            }

            if ($reviewsToCreate !== []) {
                $reviewsToCreateCount = count($reviewsToCreate);

                $this->success(
                    "Created {$reviewsToCreateCount} reviews for pattern {$pattern->id}"
                );

                $pattern->reviews()->saveMany($reviewsToCreate);

                $this->setPatternReviewChecked($pattern);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $this->error(
                "Failed to download pattern file for pattern {$pattern->id}: {$exception->getMessage()}"
            );

            $this->error('Reverting changes, deleting downloaded files if they exist...');

            $this->deleteFileIfExists($patternFilePath);

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists($patternImagesPaths);
            }
        }
    }

    /**
     * @return array<PatternReview>
     */
    protected function parseNeovimaPatternReviews(DOMXPath $xpath, Pattern $pattern): array
    {
        $this->info('Parsing reviews for pattern: ' . $pattern->id);

        $reviews = $xpath->query("//*[contains(@id, 'comments')]//*[contains(@class, 'comment-text')]");

        $toReturn = [];

        foreach ($reviews as $review) {
            $starsNodes = $xpath->query(".//strong[contains(@class, 'rating')]", $review);
            $nameNodes = $xpath->query(".//*[contains(@class, 'woocommerce-review__author')]", $review);
            $dateNodes = $xpath->query(".//*[contains(@class, 'woocommerce-review__published-date')]", $review);
            $textNodes = $xpath->query(".//*[contains(@class, 'description')]", $review);

            $stars = $starsNodes->item(0)?->textContent;

            if (!$stars) {
                $stars = null;
            }

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->attributes->getNamedItem('datetime')?->nodeValue;
            $text = $textNodes->item(0)?->textContent;

            $toReturn[] = $this->prepareReviewForCreation(
                comment: trim((string) $text),
                rating: floatval($stars),
                reviewerName: trim((string) $name),
                reviewedAt: trim((string) $date),
            );
        }

        return $toReturn;
    }
}
