<?php

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
        protected ParserServiceInterface $parserService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Parsing patterns...');

        $id = $this->option('id');

        $startedAt = now();

        $q = Pattern::query()
            ->whereHas(
                'meta',
                fn(Builder $query) => $query
                    ->where('pattern_downloaded', false)
                    ->where('is_download_url_wrong', false)
            );

        if ($id) {
            $q->where('id', $id);
        }

        $count = $q->count();

        $this->info("Found {$count} patterns to process.");

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns) {
                $patterns->each(function (Pattern $pattern) {
                    $this->info("Processing pattern: {$pattern->id}");

                    $this->processPattern($pattern);
                });
            }
        );

        $this->info('Finished parsing patterns.');

        $this->call('tools:tags-to-authors-for-patterns');

        $newCategories = PatternCategory::query()->where('created_at', '>=', $startedAt)->get();
        $newTags = PatternTag::query()->where('created_at', '>=', $startedAt)->get();
        $newAuthors = PatternAuthor::query()->where('created_at', '>=', $startedAt)->get();

        if ($newCategories->isNotEmpty()) {
            $this->info("New categories found: {$newCategories->pluck('name')->implode(', ')}");
        }

        if ($newTags->isNotEmpty()) {
            $this->info("New tags found: {$newTags->pluck('name')->implode(', ')}");
        }

        if ($newAuthors->isNotEmpty()) {
            $this->info("New authors found: {$newAuthors->pluck('name')->implode(', ')}");
        }
    }

    protected function processPattern(Pattern $pattern): void
    {
        $this->info("Pattern source: {$pattern->source->value}");

        match ($pattern->source) {
            PatternSourceEnum::NEOVIMA => (
                new \App\Console\Commands\Parsers\PatternAdapters\NeovimaPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::V_POMOSH_KOZHEVNIKU => (
                new \App\Console\Commands\Parsers\PatternAdapters\VPomoshKozhevnikuPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::ABZALA => (
                new \App\Console\Commands\Parsers\PatternAdapters\AbzalaPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::PATTERN_HUB => (
                new \App\Console\Commands\Parsers\PatternAdapters\PatternHubPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::FORMULA_KOZHI => (
                new \App\Console\Commands\Parsers\PatternAdapters\FormulaKozhiPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::PABLIK_KOZHEVNIKA => (
                new \App\Console\Commands\Parsers\PatternAdapters\PablikKozhevnikaPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::MYETSY => (
                new \App\Console\Commands\Parsers\PatternAdapters\MyEtsyPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::LASERBIZ => (
                new \App\Console\Commands\Parsers\PatternAdapters\LaserbizPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::FIRST_KOZHA => (
                new \App\Console\Commands\Parsers\PatternAdapters\FirstKozhaPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::SKINPAT => (
                new \App\Console\Commands\Parsers\PatternAdapters\SkinpatPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::LEATHER_PATTERNS => (
                new \App\Console\Commands\Parsers\PatternAdapters\LeatherPatternsPatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            PatternSourceEnum::CUTME => (
                new \App\Console\Commands\Parsers\PatternAdapters\CutmePatternAdapter($this->parserService)
            )
                ->processPattern($pattern),

            default => $this->processUnknownPattern($pattern),
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
    //             'reviewed_at' => trim($date),
    //             'comment' => trim($text),
    //         ];
    //     }

    //     return $toReturn;
    // }

    protected function processUnknownPattern(Pattern $pattern): void
    {
        $this->info("Unknown pattern source: {$pattern->source->value}, skipping...");
    }
}
