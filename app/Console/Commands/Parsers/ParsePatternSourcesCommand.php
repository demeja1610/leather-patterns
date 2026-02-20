<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers;

use App\Models\Pattern;
use App\Models\PatternTag;
use App\Models\PatternAuthor;
use App\Enum\PatternSourceEnum;
use App\Models\PatternCategory;
use App\Console\Commands\Command;
use App\Interfaces\Services\ParserServiceInterface;

class ParsePatternSourcesCommand extends Command
{
    protected $signature = 'parsers:parse-pattern-sources';

    protected $description = 'Parse pattern sources for patterns';

    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $startedAt = now();

        $this->info(message: 'Begin parsing pattern sources...');

        $sources = config(key: 'parse_sources');

        if ($sources === []) {
            $this->error(message: 'No pattern sources to parse');

            return;
        }

        $this->info(message: 'Found ' . count(value: $sources) . ' pattern sources to parse.');

        $this->info(message: 'List of pattern sources: ' . implode(separator: ', ', array: array_keys(array: $sources)));

        foreach ($sources as $patternSource => $url) {
            $source = PatternSourceEnum::from(value: $patternSource);

            $this->processSource(patternSource: $source, url: $url);
        }

        $this->success(message: 'Pattern sources parsed successfully.');

        $newPatternsCount = Pattern::query()->where(column: 'created_at', operator: '>=', value: $startedAt)->count();
        $newCategories = PatternCategory::query()->where(column: 'created_at', operator: '>=', value: $startedAt)->get();
        $newTags = PatternTag::query()->where(column: 'created_at', operator: '>=', value: $startedAt)->get();
        $newAuthors = PatternAuthor::query()->where(column: 'created_at', operator: '>=', value: $startedAt)->get();

        $this->info(message: "{$newPatternsCount} new patterns links created.");

        if ($newCategories->isNotEmpty()) {
            $this->info(message: "New categories found: {$newCategories->pluck(value: 'name')->implode(value: ', ')}");
        }

        if ($newTags->isNotEmpty()) {
            $this->info(message: "New tags found: {$newTags->pluck(value: 'name')->implode(value: ', ')}");
        }

        if ($newAuthors->isNotEmpty()) {
            $this->info(message: "New authors found: {$newAuthors->pluck(value: 'name')->implode(value: ', ')}");
        }
    }

    protected function processSource(PatternSourceEnum $patternSource, string $url): void
    {
        $this->info(message: "Processing source: {$patternSource->value}");

        match ($patternSource) {
            PatternSourceEnum::NEOVIMA => (
                new \App\Console\Commands\Parsers\SourceAdapters\NeovimaSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::V_POMOSH_KOZHEVNIKU => (
                new \App\Console\Commands\Parsers\SourceAdapters\VPomoshKozhevnikuSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::MLEATHER => (
                new \App\Console\Commands\Parsers\SourceAdapters\MLeatherSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::ABZALA => (
                new \App\Console\Commands\Parsers\SourceAdapters\AbzalaSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::PATTERN_HUB => (
                new \App\Console\Commands\Parsers\SourceAdapters\PatternHubSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::FORMULA_KOZHI => (
                new \App\Console\Commands\Parsers\SourceAdapters\FormulaKozhiSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::PABLIK_KOZHEVNIKA => (
                new \App\Console\Commands\Parsers\SourceAdapters\PablikKozhevnikaSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::MYETSY => (
                new \App\Console\Commands\Parsers\SourceAdapters\MyEtsySourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::LASERBIZ => (
                new \App\Console\Commands\Parsers\SourceAdapters\LaserbizSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::FIRST_KOZHA => (
                new \App\Console\Commands\Parsers\SourceAdapters\FirstKozhaSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::SKINPAT => (
                new \App\Console\Commands\Parsers\SourceAdapters\SkinpatSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::LEATHER_PATTERNS => (
                new \App\Console\Commands\Parsers\SourceAdapters\LeatherPatternsSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            PatternSourceEnum::CUTME => (
                new \App\Console\Commands\Parsers\SourceAdapters\CutmeSourceAdapter(
                    parserService: $this->parserService,
                ))->processSource(baseURL: $url),

            default => $this->processUnknownSource(source: $patternSource),
        };
    }

    protected function processUnknownSource(PatternSourceEnum $source): void
    {
        $this->warn(message: "Unknown pattern source: {$source->value}, skipping...");
    }
}
