<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Pattern;
use App\Jobs\DeleteFileJob;
use App\Models\PatternMeta;
use App\Enum\PatternSourceEnum;
use App\Jobs\DeleteDirectoryJob;

class PatternObserver
{
    public function created(Pattern $pattern): void
    {
        $this->createPatternMeta(pattern: $pattern);
    }

    // public function updated(Pattern $pattern): void {}

    public function deleting(Pattern $pattern): void
    {
        $this->removeImagesFiles($pattern);

        $this->removePatternFiles($pattern);
    }

    // public function deleted(Pattern $pattern): void {}

    // public function restored(Pattern $pattern): void {}

    // public function forceDeleted(Pattern $pattern): void {}

    protected function createPatternMeta(Pattern &$pattern): void
    {
        $data = [
            'pattern_id' => $pattern->id,
        ];

        if ($pattern->source === PatternSourceEnum::MLEATHER) {
            $data['is_download_url_wrong'] = true;
        }

        PatternMeta::query()->create(attributes: $data);
    }

    protected function removeImagesFiles(Pattern &$pattern): void
    {
        if ($pattern->relationLoaded('images') === false) {
            $pattern->load('images');

            $toRemove = $pattern->images->pluck('path')->toArray();
            $toRemoveDirs = [];

            if ($toRemove !== []) {
                foreach ($toRemove as $path) {
                    $dirName = dirname($path);

                    if (!isset($toRemoveDirs[$dirName])) {
                        $toRemoveDirs[$dirName] = 1;
                    }
                }

                dispatch(new DeleteFileJob(
                    path: $toRemove,
                    disk: 'public'
                ));
            }

            if ($toRemoveDirs !== []) {
                foreach (array_keys($toRemoveDirs) as $dir) {
                    dispatch(new DeleteDirectoryJob(
                        path: $dir,
                        disk: 'public'
                    ));
                }
            }
        };
    }

    protected function removePatternFiles(Pattern &$pattern): void
    {
        if ($pattern->relationLoaded('files') === false) {
            $pattern->load('files');

            $toRemove = $pattern->files->pluck('path')->toArray();
            $toRemoveDirs = [];

            if ($toRemove !== []) {
                foreach ($toRemove as $path) {
                    $dirName = dirname($path);

                    if (!isset($toRemoveDirs[$dirName])) {
                        $toRemoveDirs[$dirName] = 1;
                    }
                }

                dispatch(new DeleteFileJob(
                    path: $toRemove,
                    disk: 'public'
                ));
            }

            if ($toRemoveDirs !== []) {
                foreach (array_keys($toRemoveDirs) as $dir) {
                    dispatch(new DeleteDirectoryJob(
                        path: $dir,
                        disk: 'public'
                    ));
                }
            }
        };
    }
}
