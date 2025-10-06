<?php

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
        $baseURL = parse_url($pattern->source_url, PHP_URL_SCHEME) . '://' . parse_url($pattern->source_url, PHP_URL_HOST);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Throwable $th) {
            $this->error("Failed to parse pattern {$pattern->id}: " . $th->getMessage());

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

        $imageElements1 = $xpath->query("//*[contains(@itemprop, 'articleBody')]//img");
        $imageElements2 = $xpath->query("//*[contains(@class, 'full-image')]//img");

        /** @var DOMElement $imageElement */
        foreach ($imageElements1 as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if ($imageUrl && !str_contains($imageUrl, '.gif')) {
                $images[] = trim($baseURL, '') . '/' . trim($imageUrl, '/');
            }
        }

        /** @var DOMElement $imageElement */
        foreach ($imageElements2 as $imageElement) {
            $imageUrl = $imageElement->getAttribute('src');

            if ($imageUrl && !str_contains($imageUrl, '.gif')) {
                $images[] = trim($baseURL, '') . '/' . trim($imageUrl, '/');
            }
        }

        $categoriesElements = $xpath->query("//*[contains(@class, 'category-name')]//a");

        /** @var DOMElement $categoryElement */
        foreach ($categoriesElements as $categoryElement) {
            $categories[] = $categoryElement->textContent;
        }

        $tagsElements = $xpath->query("//*[contains(@class, 'tags')]//a");

        /** @var DOMElement $tagElement */
        foreach ($tagsElements as $tagElement) {
            $tags[] = $tagElement->textContent;
        }

        $title = $xpath->query("//h1[contains(@itemprop, 'name')]")->item(0)?->textContent;

        if (!$title) {
            $title = 'No title';
        }

        $title = trim($title);

        $downloadLinks = $xpath->query("//*[contains(@class, 'at_url')]");
        $downloadUrls = [];

        if ($downloadLinks->length > 0) {
            /** @var DOMElement $downloadLink */
            foreach ($downloadLinks as $downloadLink) {
                $downloadUrls[] = $downloadLink->getAttribute('href');
            }
        }

        if ($downloadLinks->length === 0) {
            $this->warn("No download URL found for pattern {$pattern->id}, skipping...");

            $this->setDownloadUrlWrong($pattern);

            return;
        }

        $patternFilePaths = [];
        $patternImagesPaths = [];

        try {
            $fileDownloadUrls = $downloadUrls;

            foreach ($fileDownloadUrls as $fileDownloadUrl) {
                if (str_contains($fileDownloadUrl, 'youtu')) {
                    $this->warn("YouTube video detected, skipping file download...");

                    $this->setDownloadUrlWrong($pattern);

                    return;
                }

                $patternFilePaths[] = $this->downloadPatternFile(
                    pattern: $pattern,
                    url: $fileDownloadUrl,
                );
            }

            $patternFilePaths = array_filter($patternFilePaths);

            if ($patternFilePaths === []) {
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
                filePaths: $patternFilePaths
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

                $this->success("Created $videosToCreateCount videos for pattern {$pattern->id}");

                $pattern->videos()->saveMany($videosToCreate);

                $this->setPatternVideoChecked($pattern);
            }

            if ($reviewsToCreate !== []) {
                $reviewsToCreateCount = count($reviewsToCreate);

                $this->success("Created $reviewsToCreateCount reviews for pattern {$pattern->id}");

                $pattern->reviews()->saveMany($reviewsToCreate);

                $this->setPatternReviewChecked($pattern);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error("Failed to download pattern file for pattern {$pattern->id}: {$e->getMessage()}");

            $this->error('Reverting changes, deleting downloaded files if they exist...');

            if ($patternFilePaths !== []) {
                foreach ($patternFilePaths as $patternFilePath) {
                    $this->deleteFileIfExists($patternFilePath);
                }
            }

            if ($patternImagesPaths !== []) {
                $this->deleteImagesIfExists($patternImagesPaths);
            }
        }
    }

    /**
     * @return array<PatternReview>
     */
    protected function parse1KozhaPatternReviews(DOMXPath $xpath, Pattern $pattern): array
    {
        $this->info('Parsing reviews for pattern: ' . $pattern->id);

        $reviews = $xpath->query("//*[contains(@id, 'jcm-comments')]//*[contains(@class, 'jcm-block')]");

        $toReturn = [];

        foreach ($reviews as $review) {
            $nameNodes = $xpath->query(".//*[contains(@class, 'jcm-user-cm')]//span", $review);
            $dateNodes = $xpath->query(".//*[contains(@class, 'jcm-post-header')]//meta", $review);
            $textNodes = $xpath->query(".//*[contains(@class, 'jcm-post-body')]", $review);

            $stars = null;

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->attributes->getNamedItem('content')?->nodeValue;
            $text = $textNodes->item(0)?->textContent;

            $toReturn[] = $this->prepareReviewForCreation(
                rating: floatval($stars),
                reviewerName: trim($name),
                reviewedAt: trim($date),
                comment: trim($text),
            );
        }

        return $toReturn;
    }
}
