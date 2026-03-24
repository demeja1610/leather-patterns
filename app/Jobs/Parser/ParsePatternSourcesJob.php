<?php

namespace App\Jobs\Parser;

use App\Jobs\InfoJob;
use App\Enum\PatternSourceEnum;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Interfaces\Services\ParserServiceInterface;

class ParsePatternSourcesJob extends InfoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?PatternSourceEnum $source = null) {}

    public function handle(ParserServiceInterface $parserService): void
    {
        $this->info('Begin parsing pattern sources');

        $sources = $this->source !== null
            ? [$this->source]
            : array_filter(
                array_map(
                    array: array_keys(config('parse_sources', [])),
                    callback: fn(string $item) => PatternSourceEnum::tryFrom($item)
                )
            );

        if ($sources === []) {
            $this->error('No pattern sources to parse');

            return;
        }

        $this->info('Found ' . count($sources) . ' pattern sources to parse.');

        $this->info('List of pattern sources: ' . implode(', ', array_keys($sources)));

        foreach ($sources as $patternSource) {
            $this->processSource($patternSource, $parserService);
        }
    }

    protected function processSource(PatternSourceEnum &$patternSource, ParserServiceInterface &$parserService): void
    {
        $this->info(message: "Processing source: {$patternSource->value}");

        match ($patternSource) {
            PatternSourceEnum::LEATHER_PATTERNS => (
                new \App\Parsers\PatternSource\LeatherPatternsSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::CUTME => (
                new \App\Parsers\PatternSource\CutMeSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::V_POMOSH_KOZHEVNIKU => (
                new \App\Parsers\PatternSource\VPomoshKozhevnikuSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::FORMULA_KOZHI => (
                new \App\Parsers\PatternSource\FormulaKozhiSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::PATTERN_HUB => (
                new \App\Parsers\PatternSource\PatternHubSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::PABLIK_KOZHEVNIKA => (
                new \App\Parsers\PatternSource\PablikKozhevnikaSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::SKINPAT => (
                new \App\Parsers\PatternSource\SkinPatSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::FIRST_KOZHA => (
                new \App\Parsers\PatternSource\FirstKojaSourceParser(
                    parserService: $parserService,
                ))->processSource(),

            PatternSourceEnum::MYETSY => (
                new \App\Parsers\PatternSource\MyEtsySourceParser(
                    parserService: $parserService,
                ))->processSource(),

            // PatternSourceEnum::NEOVIMA => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\NeovimaSourceAdapter(
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

            // PatternSourceEnum::LASERBIZ => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\LaserbizSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            // PatternSourceEnum::SKINPAT => (
            //     new \App\Console\Commands\Parsers\SourceAdapters\SkinpatSourceAdapter(
            //         parserService: $this->parserService,
            //     ))->processSource(baseURL: $url),

            default => $this->processUnknownSource(source: $patternSource),
        };
    }

    protected function processUnknownSource(PatternSourceEnum $source): void
    {
        $this->warn("Unknown pattern source: {$source->value}, skipping");
    }
}
