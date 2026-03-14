<?php

declare(strict_types=1);

namespace App\Parsers;

use Throwable;
use App\Models\Pattern;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Dto\Parser\Pattern\TagListDto;
use App\Dto\Parser\Pattern\FileListDto;
use App\Dto\Parser\Pattern\ImageListDto;
use App\Dto\Parser\Pattern\VideoListDto;
use App\Interfaces\Parsers\PatternParserInterface;
use App\Interfaces\Services\ParserServiceInterface;

abstract class PatternParser implements PatternParserInterface
{
    private readonly bool $runningInConsole;

    public function __construct(
        private readonly ParserServiceInterface $parserService,
    ) {
        $this->runningInConsole = App::runningInConsole();
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

    protected function log(string $type, string $message, array $context = []): void
    {
        match ($type) {
            'success' => Log::info($message, $context),
            'warn' => Log::warning($message, $context),
            'error' => Log::error($message, $context),
            'info' => Log::info($message, $context),
            default => Log::info($message, $context),
        };

        if ($this->isRunningInConsole()) {
            match ($type) {
                'success' => $this->echoSuccess($message),
                'warn' => $this->echoWarn($message),
                'error' => $this->echoError($message),
                'info' => $this->echoInfo($message),
                default => $this->echoInfo($message),
            };
        }
    }

    protected function logParsePattern(Pattern &$pattern): void
    {
        $this->log('info', "Parsing pattern with ID: {$pattern->id}", [
            'pattern' => $pattern->toArray(),
        ]);
    }

    protected function logFailedToParsePattern(Pattern &$pattern, Throwable &$th): void
    {
        $this->log('info', "Failed to parse pattern with ID: {$pattern->id}", [
            'pattern' => $pattern->toArray(),
            'error' => $th->__toString(),
        ]);
    }

    protected function logSearchForVideos(Pattern &$pattern): void
    {
        $this->log('info', "Search for videos for pattern with ID: {$pattern->id}");
    }

    protected function logFoundedVideos(VideoListDto &$videos, Pattern &$pattern): void
    {
        $this->log('info', "Found: {$videos->count()} videos for pattern with ID: {$pattern->id}", [
            'videos' => $videos->toArray(),
        ]);
    }

    protected function logSearchForImages(Pattern &$pattern): void
    {
        $this->log('info', "Search for images for pattern with ID: {$pattern->id}");
    }

    protected function logFoundImages(ImageListDto &$images, Pattern &$pattern): void
    {
        $this->log('info', "Found: {$images->count()} images for pattern with ID: {$pattern->id}", [
            'images' => $images->toArray(),
        ]);
    }

    protected function logSearchForTags(Pattern &$pattern): void
    {
        $this->log('info', "Search for tags for pattern with ID: {$pattern->id}");
    }

    protected function logFoundTags(TagListDto &$tags, Pattern &$pattern): void
    {
        $this->log('info', "Found: {$tags->count()} tags for pattern with ID: {$pattern->id}", [
            'tags' => $tags->toArray(),
        ]);
    }

    protected function logSearchForTitle(Pattern &$pattern): void
    {
        $this->log('info', "Search for title for pattern with ID: {$pattern->id}");
    }

    protected function logTitle(string &$title, Pattern &$pattern): void
    {
        $this->log('info', "New title for pattern with ID: {$pattern->id} is: {$title}");
    }

    protected function logSearchForFiles(Pattern &$pattern): void
    {
        $this->log('info', "Search for files for pattern with ID: {$pattern->id}");
    }

    protected function logFoundFiles(FileListDto &$files, Pattern &$pattern): void
    {
        $this->log('info', "Found {$files->count()} files for pattern with ID: {$pattern->id}", [
            'files' => $files->toArray(),
        ]);
    }
}
