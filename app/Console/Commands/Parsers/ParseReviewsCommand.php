<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers;

use Exception;
use Throwable;
use App\Models\Pattern;
use App\Models\PatternReview;
use App\Enum\PatternSourceEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Interfaces\Services\ParserServiceInterface;

class ParseReviewsCommand extends Command
{
    protected $signature = 'parse:reviews {--id=}';

    protected $description = 'Parse pattern reviews';

    protected $sources = [
        PatternSourceEnum::NEOVIMA,
        PatternSourceEnum::MLEATHER,
    ];

    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info(string: 'Parsing reviews...');

        $id = $this->option(key: 'id');

        $q = Pattern::query()
            ->whereHas(
                relation: 'meta',
                callback: fn($query) => $query
                    ->where(column: 'reviews_updated_at', operator: '<', value: now()->subDays(14))
                    ->orWhereNull('reviews_updated_at'),
            )
            ->whereIn('source', $this->sources)
            ->with([
                'reviews',
                'meta',
            ]);

        if ($id) {
            $q->where('id', $id);
        }

        $count = $q->count();

        $this->info(string: "Found {$count} patterns to check for updates on reviews");

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns): void {
                $pattern = $patterns->first();
                $allPatternReviewsOnPage = $this->processPattern(pattern: $pattern);

                if ($allPatternReviewsOnPage === []) {
                    $this->info(string: "No reviews found for pattern: " . $pattern->id);

                    $pattern->meta->update(['reviews_updated_at' => now()]);

                    return;
                }

                $existingPatternReviews = $pattern->reviews->toArray();
                $toCreate = [];

                foreach ($allPatternReviewsOnPage as $review) {
                    $isAlreadyExists = array_filter(
                        array: $existingPatternReviews,
                        callback: fn(array $patternReview): bool => $patternReview['comment'] === $review['comment'],
                    );

                    if ($isAlreadyExists !== []) {
                        continue;
                    }

                    $toCreate[] = new PatternReview(attributes: $review);
                }

                if ($toCreate === []) {
                    $this->info(string: "No new reviews found for pattern: " . $pattern->id);

                    $pattern->meta->update(['reviews_updated_at' => now()]);

                    return;
                }

                try {
                    DB::beginTransaction();

                    $pattern->reviews()->saveMany($toCreate);

                    $pattern->meta->update(['reviews_updated_at' => now()]);

                    $this->call(command: 'tools:pattern:calculate-average-rating', arguments: [
                        '--id' => $pattern->id,
                    ]);

                    DB::commit();
                } catch (Throwable $throwable) {
                    DB::rollBack();

                    $this->error(
                        string: 'Error inserting reviews: ' . $throwable->getMessage(),
                    );
                }
            },
        );

        $this->info(string: "Finish parsing reviews");

        return Command::SUCCESS;
    }

    protected function processPattern(Pattern $pattern): array
    {
        return match ($pattern->source) {
            PatternSourceEnum::NEOVIMA => $this->parseNeovimaPatternReviews(pattern: $pattern),

            PatternSourceEnum::MLEATHER => $this->parseMLeatherPatternReviews(pattern: $pattern),

            default => [],
        };
    }

    protected function parseNeovimaPatternReviews(Pattern $pattern): array
    {
        $this->info(string: 'Parsing reviews for pattern: ' . $pattern->id);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Exception $exception) {
            $this->error(
                string: "Error getting page content for pattern {$pattern->id}: {$exception->getMessage()}",
            );

            return [];
        }

        if (str_contains(haystack: $content, needle: 'Отзывов пока нет.')) {
            $this->info(string: 'No reviews found for pattern: ' . $pattern->id);

            return [];
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

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

            $toReturn[] = [
                'rating' => floatval(value: $stars),
                'reviewer_name' => trim(string: (string) $name),
                'comment' => trim(string: (string) $text),
            ];
        }

        return $toReturn;
    }

    protected function parseMLeatherPatternReviews(Pattern $pattern): array
    {
        $this->info(string: 'Parsing reviews for pattern: ' . $pattern->id);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Exception $exception) {
            $this->error(
                string: "Error getting page content for pattern {$pattern->id}: {$exception->getMessage()}",
            );

            return [];
        }

        if (str_contains(haystack: $content, needle: 'Отзывов еще никто не оставлял')) {
            $this->info(string: 'No reviews found for pattern: ' . $pattern->id);

            return [];
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

        $reviews = $xpath->query(expression: "//*[contains(@class, 'reviews')]//*[contains(@class, 'masonry-reviews-item')]");

        $toReturn = [];

        foreach ($reviews as $review) {
            $nameNodes = $xpath->query(expression: ".//*[contains(@class, 'author')]", contextNode: $review);
            $dateNodes = $xpath->query(expression: ".//*[contains(@class, 'date')]", contextNode: $review);
            $textNodes = $xpath->query(expression: ".//*[contains(@class, 'review-content')]", contextNode: $review);

            $stars = null;

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->textContent;
            $text = $textNodes->item(0)?->textContent;

            $toReturn[] = [
                'rating' => floatval(value: $stars),
                'reviewer_name' => trim(string: (string) $name),
                'comment' => trim(string: (string) $text),
            ];
        }

        $unique = [];

        foreach ($toReturn as $item) {
            $unique[$item['comment']] = $item;
        }

        return array_values(array: $unique);
    }
}
