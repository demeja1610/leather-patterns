<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use Carbon\Carbon;
use App\Models\Pattern;
use App\Models\PatternMeta;
use Illuminate\Support\Facades\Log;
use App\Interfaces\Services\FileServiceInterface;
use App\Interfaces\Parsers\PatternParserInterface;
use App\Interfaces\Services\ParserServiceInterface;

abstract class PatternParser implements PatternParserInterface
{
    protected readonly Carbon $now;
    public function __construct(
        private readonly ParserServiceInterface $parserService,
        private readonly FileServiceInterface $fileService,
    ) {
        $this->now = Carbon::now();
    }

    public function getParserService(): ParserServiceInterface
    {
        return $this->parserService;
    }

    public function getFileService(): FileServiceInterface
    {
        return $this->fileService;
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

    protected function setPatternFilesDownloaded(Pattern &$pattern): void
    {
        $this->logSetPatternFilesDownloaded($pattern);

        if ($pattern->relationLoaded('meta') === false) {
            $pattern->load('meta');
        }

        if ($pattern->meta instanceof PatternMeta) {
            $pattern->meta->pattern_downloaded = true;

            $pattern->meta->save();
        }
    }

    protected function setPatternImagesDownloaded(Pattern &$pattern): void
    {
        $this->logSetPatternImagesDownloaded($pattern);

        if ($pattern->relationLoaded('meta') === false) {
            $pattern->load('meta');
        }

        if ($pattern->meta instanceof PatternMeta) {
            $pattern->meta->images_downloaded = true;

            $pattern->meta->save();
        }
    }

    protected function setPatternVideoChecked(Pattern &$pattern): void
    {
        $this->logSetPatternVideoChecked($pattern);

        if ($pattern->relationLoaded('meta') === false) {
            $pattern->load('meta');
        }

        if ($pattern->meta instanceof PatternMeta) {
            $pattern->meta->is_video_checked = true;

            $pattern->meta->save();
        }
    }

    protected function setPatternReviewsUpdatedAt(Pattern $pattern): void
    {
        $this->logSetPatternReviewsUpdatedAt($pattern);

        if ($pattern->relationLoaded('meta') === false) {
            $pattern->load('meta');
        }

        if ($pattern->meta instanceof PatternMeta) {
            $pattern->meta->reviews_updated_at = $this->now;

            $pattern->meta->save();
        }
    }

    protected function setDownloadUrlWrong(Pattern $pattern): void
    {
        $this->logSetPatternDownloadUrlWrong($pattern);

        if ($pattern->relationLoaded('meta') === false) {
            $pattern->load('meta');
        }

        if ($pattern->meta instanceof PatternMeta) {
            $pattern->meta->is_download_url_wrong = true;

            $pattern->meta->save();
        }
    }

    protected function logSetPatternFilesDownloaded(Pattern &$pattern): void
    {
        $message = "Setting pattern meta files downloaded for pattern with ID: {$pattern->id} to true";

        Log::info($message);

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logSetPatternImagesDownloaded(Pattern &$pattern): void
    {
        $message = "Setting pattern meta images downloaded for pattern with ID: {$pattern->id} to true";

        Log::info($message);

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logSetPatternVideoChecked(Pattern &$pattern): void
    {
        $message = "Setting pattern meta video checked for pattern with ID: {$pattern->id} to true";

        Log::info($message);

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logSetPatternReviewsUpdatedAt(Pattern &$pattern): void
    {
        $message = "Setting pattern meta reviews updated at for pattern with ID: {$pattern->id} to {$this->now->toDateTimeString()}";

        Log::info($message);

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }

    protected function logSetPatternDownloadUrlWrong(Pattern &$pattern): void
    {
        $message = "Setting pattern meta download URL wrong for pattern with ID: {$pattern->id} to true";

        Log::info($message);

        if ($this->isRunningInConsole()) {
            $this->echoInfo($message);
        }
    }
}
