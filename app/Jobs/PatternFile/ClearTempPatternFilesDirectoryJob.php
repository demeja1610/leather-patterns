<?php

namespace App\Jobs\PatternFile;

use App\Models\PatternFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClearTempPatternFilesDirectoryJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'delete pattern files in temporary directory';

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $newPatternFile = new PatternFile();

        $tempPath = $newPatternFile->getUploadPath();

        $publicTempPath = Storage::disk($newPatternFile->getSaveToDiskName())->path($tempPath);

        Log::info(ucfirst($this->actionName) . ". Directory is: {$publicTempPath}");

        if (Storage::disk($newPatternFile->getSaveToDiskName())->exists($tempPath)) {
            $filesList = Storage::disk($newPatternFile->getSaveToDiskName())->allFiles($tempPath);

            $filesListCount = count($filesList);

            Log::info(ucfirst($this->actionName) . ". Directory contains {$filesListCount} files");

            $deleted = Storage::disk($newPatternFile->getSaveToDiskName())->delete($filesList);

            if ($deleted === true) {
                Log::info(ucfirst($this->actionName) . ". All files in directory {$publicTempPath} deleted");
            }
        } else {
            Log::info(ucfirst($this->actionName) . ". Temporary directory doesn't exists");
        }

        Log::info("Finish {$this->actionName}");
    }
}
