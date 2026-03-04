<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteFileJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'delete file(s)';

    /**
     * @param string|array<string> $path
     */
    public function __construct(
        public string|array $path,
        public ?string $disk = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        if ($this->disk !== null) {
            Log::info(ucfirst($this->actionName) . ". Disk is: {$this->disk}");
        }

        $paths = is_array($this->path)
            ? $this->path
            : [$this->path];

        $fullPaths = array_map(
            array: $paths,
            callback: fn(string $path) => Storage::disk($this->disk)->path($path),
        );

        $fullPathsStr = implode(
            array: $fullPaths,
            separator: ', ',
        );

        Log::info(ucfirst($this->actionName) . ". Files: {$fullPathsStr}");

        $result = Storage::disk($this->disk)->delete($paths);

        if ($result) {
            Log::info(ucfirst($this->actionName) . ". Deleted: {$fullPathsStr}");
        } else {
            Log::warning(ucfirst($this->actionName) . ". NOT deleted: {$fullPathsStr}");
        }
    }
}
