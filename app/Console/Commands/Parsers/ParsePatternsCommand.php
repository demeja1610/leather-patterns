<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers;

use App\Models\Pattern;
use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Enum\PatternSourceEnum;
use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Interfaces\Services\ParserServiceInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ParsePatternsCommand extends Command
{
    protected $signature = 'parsers:parse-patterns {--id=}';

    protected $description = 'Parse patterns and files';

    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info(string: 'Parsing patterns...');

        $id = $this->option(key: 'id');

        $startedAt = now();

        $q = Pattern::query()
            ->whereHas(
                relation: 'meta',
                callback: fn(Builder $query) => $query
                    ->where('pattern_downloaded', false)
                    ->where('is_download_url_wrong', false),
            );

        if ($id) {
            $q->where('id', $id);
        }

        $count = $q->count();

        $this->info(string: "Found {$count} patterns to process.");

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns): void {
                $patterns->each(callback: function (Pattern $pattern): void {
                    $this->info(string: "Processing pattern: {$pattern->id}");

                    $this->processPattern(pattern: $pattern);
                });
            },
        );

        $this->info(string: 'Finished parsing patterns.');

        $newCategories = PatternCategory::query()->where(column: 'created_at', operator: '>=', value: $startedAt)->get();
        $newTags = PatternTag::query()->where(column: 'created_at', operator: '>=', value: $startedAt)->get();
        $newAuthors = PatternAuthor::query()->where(column: 'created_at', operator: '>=', value: $startedAt)->get();

        if ($newCategories->isNotEmpty()) {
            $this->info(string: "New categories found: {$newCategories->pluck(value: 'name')->implode(value: ', ')}");
        }

        if ($newTags->isNotEmpty()) {
            $this->info(string: "New tags found: {$newTags->pluck(value: 'name')->implode(value: ', ')}");
        }

        if ($newAuthors->isNotEmpty()) {
            $this->info(string: "New authors found: {$newAuthors->pluck(value: 'name')->implode(value: ', ')}");
        }
    }

    protected function processPattern(Pattern $pattern): void
    {
        $this->info(string: "Pattern source: {$pattern->source->value}");

        match ($pattern->source) {
            PatternSourceEnum::NEOVIMA => (
                new \App\Console\Commands\Parsers\PatternAdapters\NeovimaPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::V_POMOSH_KOZHEVNIKU => (
                new \App\Console\Commands\Parsers\PatternAdapters\VPomoshKozhevnikuPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::ABZALA => (
                new \App\Console\Commands\Parsers\PatternAdapters\AbzalaPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::PATTERN_HUB => (
                new \App\Console\Commands\Parsers\PatternAdapters\PatternHubPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::FORMULA_KOZHI => (
                new \App\Console\Commands\Parsers\PatternAdapters\FormulaKozhiPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::PABLIK_KOZHEVNIKA => (
                new \App\Console\Commands\Parsers\PatternAdapters\PablikKozhevnikaPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::MYETSY => (
                new \App\Console\Commands\Parsers\PatternAdapters\MyEtsyPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::LASERBIZ => (
                new \App\Console\Commands\Parsers\PatternAdapters\LaserbizPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::FIRST_KOZHA => (
                new \App\Console\Commands\Parsers\PatternAdapters\FirstKozhaPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::SKINPAT => (
                new \App\Console\Commands\Parsers\PatternAdapters\SkinpatPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::LEATHER_PATTERNS => (
                new \App\Console\Commands\Parsers\PatternAdapters\LeatherPatternsPatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

            PatternSourceEnum::CUTME => (
                new \App\Console\Commands\Parsers\PatternAdapters\CutmePatternAdapter(parserService: $this->parserService)
            )
                ->processPattern(pattern: $pattern),

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

    protected function processUnknownPattern(Pattern $pattern): void
    {
        $this->info(string: "Unknown pattern source: {$pattern->source->value}, skipping...");
    }
}
