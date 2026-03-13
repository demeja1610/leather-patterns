<?php

namespace App\Observers;

use App\Jobs\DeleteFileJob;
use App\Models\PatternImage;
use Illuminate\Support\Facades\Storage;

class PatternImageObserver
{
    public function creating(PatternImage $patternImage): void
    {
        $newPath = trim($patternImage->getUploadPath(), '/');

        $name = basename($patternImage->path);

        $newImagePath = "{$newPath}/{$name}";

        $moved =  Storage::disk($patternImage->getSaveToDiskName())->move($patternImage->path, $newImagePath);

        if ($moved === true) {
            $patternImage->path = $newImagePath;
        }
    }

    public function deleting(PatternImage $patternImage): void
    {
        dispatch(new DeleteFileJob(
            path: $patternImage->path,
            disk: $patternImage->getSaveToDiskName(),
        ));
    }
}
