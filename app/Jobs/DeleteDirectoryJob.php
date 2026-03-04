<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteDirectoryJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'delete directory';

    public function __construct(
        public string $path,
        public ?string $disk = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        if ($this->disk !== null) {
            Log::info(ucfirst($this->actionName) . ". Disk is: {$this->disk}");
        }

        $fullPath = Storage::disk($this->disk)->path($this->path);

        Log::info(ucfirst($this->actionName) . ". Directory: {$fullPath}");

        $result = Storage::disk($this->disk)->deleteDirectory($this->path);

        if ($result) {
            Log::info(ucfirst($this->actionName) . ". Deleted: {$fullPath}");
        } else {
            Log::warning(ucfirst($this->actionName) . ". NOT deleted: {$fullPath}");
        }
    }
}
