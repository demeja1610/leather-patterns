<?php

declare(strict_types=1);

namespace App\Parsers;

use Throwable;
use App\Models\Pattern;
use App\Enum\PatternSourceEnum;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Dto\Parser\Pattern\ParsedPatternLinkDto;
use App\Interfaces\Services\ParserServiceInterface;
use App\Interfaces\Parsers\PatternSourceParserInterface;
use App\Jobs\Parser\Pattern\CreatePatternFromParsedPatternLinkJob;

abstract class PatternSourceParser implements PatternSourceParserInterface
{
    private readonly bool $runningInConsole;

    public function __construct(
        private readonly ParserServiceInterface $parserService,
    ) {
        $this->runningInConsole = App::runningInConsole();
    }

    public function createPattern(ParsedPatternLinkDto $patternLink): void
    {
        if ($this->isRunningInConsole()) {
            $this->echoInfo("Creating pattern with URL: {$patternLink->getSourceUrl()}");
        }

        CreatePatternFromParsedPatternLinkJob::dispatch($patternLink);
    }

    public function getParserService(): ParserServiceInterface
    {
        return $this->parserService;
    }

    public function isRunningInConsole(): bool
    {
        return $this->runningInConsole;
    }

    public function echoInfo(string $message): void
    {
        echo "\033[36m{$message}\033[0m" . PHP_EOL;
    }

    public function echoError($message): void
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    public function echoWarn($message): void
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    public function echoSuccess($message): void
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }

    public function isPatternExists(ParsedPatternLinkDto $patternLink): bool
    {
        return Pattern::query()->where('source_url', $patternLink->getSourceUrl())->exists();
    }

    protected function logGettingSourceUrl(PatternSourceEnum &$patternSource): void
    {
        $message = "Getting URL for pattern source: {$patternSource->value}";

        Log::info(
            message: $message,
            context: [
                'source' => $patternSource->value,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logEmptySourceUrl(PatternSourceEnum &$patternSource): void
    {
        $message = "Pattern source: {$patternSource->value} URL not found";

        Log::warning(
            message: $message,
            context: [
                'source' => $patternSource->value,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoWarn($message);
        }
    }

    protected function logPatternSourceUrl(PatternSourceEnum &$patternSource, string &$url): void
    {
        $message = "Pattern source: {$patternSource->value} URL is: {$url}";

        Log::info(
            message: $message,
            context: [
                'source' => $patternSource->value,
                'url' => $url,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logProcessingUrl(string &$url): void
    {
        $message = "Processing URL: {$url}";

        Log::info(
            message: $message,
            context: [
                'url' => $url,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logErrorProcessingUrl(string &$url, Throwable &$e): void
    {
        $message = "An error happened while processing url: {$url}";

        Log::error(
            message: $message,
            context: [
                'url' => $url,
                'error' => $e->__toString(),
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoError($message);
        }
    }

    protected function logFoundedPatternsElementsOnPageWithUrl(string &$url, $count): void
    {
        $message = "On page with URL: {$url} found {$count} pattern elements";

        Log::info(
            message: $message,
            context: [
                'url' => $url,
                'count' => $count,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logNoPatternsElementsOnPageWithUrl(string &$url): void
    {
        $message = "On page with URL: {$url} no pattern elements found";

        Log::warning(
            message: $message,
            context: [
                'url' => $url,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoWarn($message);
        }
    }

    protected function logExistingAndCreatedPatternsCount(int &$totalCount, int &$existingCount, int &$createdCount): void
    {
        $message = "From {$totalCount} pattern links {$existingCount} already exists and {$createdCount} was created";

        Log::info(
            message: $message,
            context: [
                'total_count' => $totalCount,
                'existing_count' => $existingCount,
                'created_count' => $createdCount,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logAllUnknownPatternsParsed(PatternSourceEnum &$patternSource): void
    {
        $message = "All NEW patterns from source: {$patternSource->value} parsed";

        Log::info(
            message: $message,
            context: [
                'source' => $patternSource->value,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logNextPageLinkNotFound(string &$url): void
    {
        $message = "URL to the next page not found on page with URL: {$url}";

        Log::warning(
            message: $message,
            context: [
                'url' => $url,
            ]
        );

        if ($this->isRunningInConsole()) {
            $this->echoWarn($message);
        }
    }
}
