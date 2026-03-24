<?php

namespace App\Jobs\Parser;

use App\Jobs\InfoJob;
use App\Models\Pattern;
use App\Enum\PatternSourceEnum;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Interfaces\Services\ParserServiceInterface;

class ParsePatternsJob extends InfoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?int $id = null) {}

    public function handle(ParserServiceInterface $parserService): void
    {
        $this->info('Parsing patterns.');

        $q = Pattern::query();

        if ($this->id) {
            $q->where('id', $this->id);
        } else {
            $q->whereHas(
                relation: 'meta',
                callback: fn(Builder $query) => $query
                    ->where('pattern_downloaded', false)
                    ->where('is_download_url_wrong', false),
            );
        }

        $count = $q->count();

        $this->info("Found {$count} patterns to process.");

        $q->chunkById(
            count: 250,
            callback: fn(Collection $patterns) => $patterns->each(
                fn(Pattern $pattern) => $this->processPattern($pattern, $parserService)
            ),
        );
    }

    protected function processPattern(Pattern &$pattern, ParserServiceInterface &$parserService): void
    {
        $this->info("Processing pattern with ID: {$pattern->id} and URL: {$pattern->url}");

        match ($pattern->source) {
            PatternSourceEnum::LEATHER_PATTERNS => (
                new \App\Parsers\Pattern\LeatherPatternsPatternParser($parserService)
            )->processPattern($pattern),

            PatternSourceEnum::CUTME => (
                new \App\Parsers\Pattern\CutMePatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::V_POMOSH_KOZHEVNIKU => (
                new \App\Parsers\Pattern\VPomoshKozhevnikuPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::FORMULA_KOZHI => (
                new \App\Parsers\Pattern\FormulaKozhiPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::PATTERN_HUB => (
                new \App\Parsers\Pattern\PatternHubPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::PABLIK_KOZHEVNIKA => (
                new \App\Parsers\Pattern\PablikKozhevnikaPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::SKINPAT => (
                new \App\Parsers\Pattern\SkinPatPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::FIRST_KOZHA => (
                new \App\Parsers\Pattern\FirstKojaPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::MYETSY => (
                new \App\Parsers\Pattern\MyEtsyPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::LASERBIZ => (
                new \App\Parsers\Pattern\LaserbizPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::ABZALA => (
                new \App\Parsers\Pattern\AbzalaPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            PatternSourceEnum::NEOVIMA => (
                new \App\Parsers\Pattern\NeovimaPatternParser($parserService)
            )->processPattern(pattern: $pattern),

            // PatternSourceEnum:: => (
            //     new \App\Console\Commands\Parsers\PatternAdapters\NeovimaPatternAdapter(parserService: $this->parserService)
            // )
            //     ->processPattern(pattern: $pattern),

            // PatternSourceEnum::FORMULA_KOZHI => (
            //     new \App\Console\Commands\Parsers\PatternAdapters\FormulaKozhiPatternAdapter(parserService: $this->parserService)
            // )
            //     ->processPattern(pattern: $pattern),

            default => $this->processUnknownPattern(pattern: $pattern),
        };
    }

    /**
     * Commented until we ca download patterns from sources like VK or Google Drive
     */
    // protected function parseMleatherPatternReviews(string $content, Pattern $pattern): array
    // {
    //     $this->info('Parsing reviews for pattern: ' . $pattern->id);

    //     if (str_contains($content, 'Отзывов еще никто не оставлял')) {
    //         $this->info('No reviews found for pattern: ' . $pattern->id);

    //         return [];
    //     }

    //     $dom = $this->parserService->parseDOM($content);
    //     $xpath = $this->parserService->getDOMXPath($dom);

    //     $reviews = $xpath->query("//*[contains(@id, 'reviews')]//*[contains(@class, 'masonry-reviews-item')]");

    //     $toReturn = [];

    //     foreach ($reviews as $review) {
    //         $nameNodes = $xpath->query(".//*[contains(@class, 'author')]", $review);
    //         $dateNodes = $xpath->query(".//*[contains(@class, 'date')]", $review);
    //         $textNodes = $xpath->query(".//*[contains(@class, 'review-content')]", $review);

    //         $stars = null;

    //         $name = $nameNodes->item(0)?->textContent;
    //         $date = $dateNodes->item(0)?->textContent;
    //         $text = $textNodes->item(0)?->textContent;

    //         $toReturn[] = [
    //             'rating' => floatval($stars),
    //             'reviewer_name' => trim($name),
    //             'comment' => trim($text),
    //         ];
    //     }

    //     return $toReturn;
    // }

    protected function processUnknownPattern(Pattern &$pattern): void
    {
        $this->info("Unknown pattern source: {$pattern->source->value}, skipping.");
    }
}
