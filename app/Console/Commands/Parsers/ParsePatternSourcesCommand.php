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
        protected ParserServiceInterface $parserService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $startedAt = now();

        $this->info('Begin parsing pattern sources...');

        $sources = config('parse_sources');

        if ($sources === []) {
            $this->error('No pattern sources to parse');

            return;
        }

        $this->info('Found ' . count($sources) . ' pattern sources to parse.');

        $this->info('List of pattern sources: ' . implode(', ', array_keys($sources)));

        foreach ($sources as $patternSource => $url) {
            $source = PatternSourceEnum::from($patternSource);

            $this->processSource($source, $url);
        }

        $this->success('Pattern sources parsed successfully.');

        $newPatternsCount = Pattern::query()->where('created_at', '>=', $startedAt)->count();
        $newCategories = PatternCategory::query()->where('created_at', '>=', $startedAt)->get();
        $newTags = PatternTag::query()->where('created_at', '>=', $startedAt)->get();
        $newAuthors = PatternAuthor::query()->where('created_at', '>=', $startedAt)->get();

        $this->info("{$newPatternsCount} new patterns links created.");

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

    protected function processSource(PatternSourceEnum $patternSource, string $url): void
    {
        $this->info("Processing source: {$patternSource->value}");

        match ($patternSource) {
            PatternSourceEnum::NEOVIMA => (
                new \App\Console\Commands\Parsers\SourceAdapters\NeovimaSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::V_POMOSH_KOZHEVNIKU => (
                new \App\Console\Commands\Parsers\SourceAdapters\VPomoshKozhevnikuSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::MLEATHER => (
                new \App\Console\Commands\Parsers\SourceAdapters\MLeatherSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::ABZALA => (
                new \App\Console\Commands\Parsers\SourceAdapters\AbzalaSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::PATTERN_HUB => (
                new \App\Console\Commands\Parsers\SourceAdapters\PatternHubSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::FORMULA_KOZHI => (
                new \App\Console\Commands\Parsers\SourceAdapters\FormulaKozhiSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::PABLIK_KOZHEVNIKA => (
                new \App\Console\Commands\Parsers\SourceAdapters\PablikKozhevnikaSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::MYETSY => (
                new \App\Console\Commands\Parsers\SourceAdapters\MyEtsySourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::LASERBIZ => (
                new \App\Console\Commands\Parsers\SourceAdapters\LaserbizSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::FIRST_KOZHA => (
                new \App\Console\Commands\Parsers\SourceAdapters\FirstKozhaSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::SKINPAT => (
                new \App\Console\Commands\Parsers\SourceAdapters\SkinpatSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::LEATHER_PATTERNS => (
                new \App\Console\Commands\Parsers\SourceAdapters\LeatherPatternsSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            PatternSourceEnum::CUTME => (
                new \App\Console\Commands\Parsers\SourceAdapters\CutmeSourceAdapter(
                    parserService: $this->parserService
                ))->processSource(baseURL: $url),

            default => $this->processUnknownSource($patternSource),
        };
    }

    protected function processUnknownSource(PatternSourceEnum $source): void
    {
        $this->warn("Unknown pattern source: {$source->value}, skipping...");
    }
}
