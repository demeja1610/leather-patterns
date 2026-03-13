<?php

namespace App\Observers;

use App\Jobs\DeleteFileJob;
use App\Models\PatternFile;
use Illuminate\Support\Facades\Storage;

class PatternFileObserver
{
    public function creating(PatternFile $patternFile): void
    {
        $newPath = trim($patternFile->getUploadPath(), '/');

        $name = basename($patternFile->path);

        $newFilePath = "{$newPath}/{$name}";

        $moved =  Storage::disk($patternFile->getSaveToDiskName())->move($patternFile->path, $newFilePath);

        if ($moved === true) {
            $patternFile->path = $newFilePath;
        }
    }

    public function deleting(PatternFile $patternFile): void
    {
        dispatch(new DeleteFileJob(
            path: $patternFile->path,
            disk: $patternFile->getSaveToDiskName(),
        ));
    }
}
