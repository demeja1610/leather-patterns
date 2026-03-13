<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use App\Interfaces\Services\ParserServiceInterface;

class ParsePatternSourcesCommand extends Command
{
    protected $signature = 'parsers:parse-pattern-sources {--source=}';

    protected $description = 'Parse pattern sources for patterns';

    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $sourceStr = $this->option(key: 'source');

        if ($sourceStr !== null) {
            $source = PatternSourceEnum::tryFrom($sourceStr);

            if ($source === null) {
                $this->error("Please provide one of pattern source names presented in: " . PatternSourceEnum::class);

                return self::FAILURE;
            }
        }

        $this->info(message: 'Begin parsing pattern sources...');

        $sources = isset($source)
            ? [$source]
            : array_filter(
                array_map(
                    array: array_keys(config('parse_sources', [])),
                    callback: fn(string $item) => PatternSourceEnum::tryFrom($item)
                )
            );

        if ($sources === []) {
            $this->error(message: 'No pattern sources to parse');

            return self::FAILURE;
        }

        $this->info(message: 'Found ' . count(value: $sources) . ' pattern sources to parse.');

        $this->info(message: 'List of pattern sources: ' . implode(separator: ', ', array: array_keys(array: $sources)));

        foreach ($sources as $patternSource) {
            $this->processSource($patternSource);
        }

        return self::SUCCESS;
    }

    protected function processSource(PatternSourceEnum $patternSource): void
    {
        $this->info(message: "Processing source: {$patternSource->value}");

        match ($patternSource) {
            // PatternSourceEnum::NEOVIMA => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\NeovimaSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::V_POMOSH_KOZHEVNIKU => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\VPomoshKozhevnikuSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::MLEATHER => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\MLeatherSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::ABZALA => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\AbzalaSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::PATTERN_HUB => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\PatternHubSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::FORMULA_KOZHI => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\FormulaKozhiSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::PABLIK_KOZHEVNIKA => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\PablikKozhevnikaSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::MYETSY => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\MyEtsySourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::LASERBIZ => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\LaserbizSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::FIRST_KOZHA => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\FirstKozhaSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::SKINPAT => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\SkinpatSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            PatternSourceEnum::LEATHER_PATTERNS => (
                new \App\Parsers\PatternSource\LeatherPatternsSourceParser(
                    parserService: $this->parserService,
                ))->processSource(),

            // PatternSourceEnum::CUTME => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\CutmeSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            default => $this->processUnknownSource(source: $patternSource),
        };
    }

    protected function processUnknownSource(PatternSourceEnum $source): void
    {
        $this->warn(message: "Unknown pattern source: {$source->value}, skipping...");
    }
}
