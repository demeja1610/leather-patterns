<?php

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
        protected ParserServiceInterface $parserService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Parsing reviews...');

        $id = $this->option('id');

        $q = Pattern::query()
            ->whereHas(
                'meta',
                fn($query) => $query
                    ->where('reviews_updated_at', '<', now()->subDays(14))
                    ->orWhere('reviews_updated_at', null)
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

        $this->info("Found {$count} patterns to check for updates on reviews");

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns) {
                $pattern = $patterns->first();
                $allPatternReviewsOnPage = $this->processPattern($pattern);

                if ($allPatternReviewsOnPage === []) {
                    $this->info("No reviews found for pattern: " . $pattern->id);

                    $pattern->meta->update(['reviews_updated_at' => now()]);

                    return;
                }

                $existingPatternReviews = $pattern->reviews->toArray();
                $toCreate = [];

                foreach ($allPatternReviewsOnPage as $review) {
                    $isAlreadyExists = array_filter(
                        array: $existingPatternReviews,
                        callback: function ($patternReview) use ($review) {
                            return $patternReview['comment'] === $review['comment'];
                        },
                    );

                    if (count($isAlreadyExists) > 0) {
                        continue;
                    }

                    $toCreate[] = new PatternReview($review);
                }

                if (empty($toCreate)) {
                    $this->info("No new reviews found for pattern: " . $pattern->id);

                    $pattern->meta->update(['reviews_updated_at' => now()]);

                    return;
                }

                try {
                    DB::beginTransaction();

                    $pattern->reviews()->saveMany($toCreate);

                    $pattern->meta->update(['reviews_updated_at' => now()]);

                    $this->call('tools:calculate-patterns-average-rating', [
                        '--id' => $pattern->id,
                    ]);

                    DB::commit();
                } catch (Throwable $th) {
                    DB::rollBack();

                    $this->error('Error inserting reviews: ' . $th->getMessage());
                }
            },
        );

        $this->info("Finish parsing reviews");

        return Command::SUCCESS;
    }

    protected function processPattern(Pattern $pattern): array
    {
        return match ($pattern->source) {
            PatternSourceEnum::NEOVIMA => $this->parseNeovimaPatternReviews($pattern),

            PatternSourceEnum::MLEATHER => $this->parseMLeatherPatternReviews($pattern),

            default => [],
        };
    }

    protected function parseNeovimaPatternReviews(Pattern $pattern): array
    {
        $this->info('Parsing reviews for pattern: ' . $pattern->id);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Exception $e) {
            $this->error("Error getting page content for pattern {$pattern->id}: {$e->getMessage()}");

            return [];
        }

        if (str_contains($content, 'Отзывов пока нет.')) {
            $this->info('No reviews found for pattern: ' . $pattern->id);

            return [];
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

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

            $toReturn[] = [
                'rating' => floatval($stars),
                'reviewer_name' => trim($name),
                'reviewed_at' => trim($date),
                'comment' => trim($text),
            ];
        }

        return $toReturn;
    }

    protected function parseMLeatherPatternReviews(Pattern $pattern): array
    {
        $this->info('Parsing reviews for pattern: ' . $pattern->id);

        try {
            $content = $this->parserService->parseUrl($pattern->source_url);
        } catch (Exception $e) {
            $this->error("Error getting page content for pattern {$pattern->id}: {$e->getMessage()}");

            return [];
        }

        if (str_contains($content, 'Отзывов еще никто не оставлял')) {
            $this->info('No reviews found for pattern: ' . $pattern->id);

            return [];
        }

        $dom = $this->parserService->parseDOM($content);
        $xpath = $this->parserService->getDOMXPath($dom);

        $reviews = $xpath->query("//*[contains(@class, 'reviews')]//*[contains(@class, 'masonry-reviews-item')]");

        $toReturn = [];

        foreach ($reviews as $review) {
            $nameNodes = $xpath->query(".//*[contains(@class, 'author')]", $review);
            $dateNodes = $xpath->query(".//*[contains(@class, 'date')]", $review);
            $textNodes = $xpath->query(".//*[contains(@class, 'review-content')]", $review);

            $stars = null;

            $name = $nameNodes->item(0)?->textContent;
            $date = $dateNodes->item(0)?->textContent;
            $text = $textNodes->item(0)?->textContent;

            $toReturn[] = [
                'rating' => floatval($stars),
                'reviewer_name' => trim($name),
                'reviewed_at' => trim($date),
                'comment' => trim($text),
            ];
        }

        $unique = [];

        foreach ($toReturn as $item) {
            $unique[$item['comment']] = $item;
        }

        $toReturn = array_values($unique);

        return $toReturn;
    }
}
