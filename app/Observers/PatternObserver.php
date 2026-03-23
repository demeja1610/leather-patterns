<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Pattern;
use App\Jobs\DeleteFileJob;
use App\Models\PatternFile;
use App\Models\PatternMeta;
use App\Models\PatternImage;
use App\Enum\PatternSourceEnum;
use App\Jobs\DeleteDirectoryJob;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternTagsInPatternsJob;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternTagsJob;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternAuthorsInPatternsJob;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternAuthorsJob;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternCategoriesInPatternsJob;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternCategoriesJob;

class PatternObserver
{
    public function created(Pattern $pattern): void
    {
        $this->createPatternMeta(pattern: $pattern);

        if ($pattern->source !== PatternSourceEnum::LOCAL) {
            $this->clearParsedPattern($pattern);
        }
    }

    public function updated(Pattern $pattern): void
    {
        if ($pattern->source !== PatternSourceEnum::LOCAL) {
            $this->clearParsedPattern($pattern);
        }
    }

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

            $newPatternImage = new PatternImage();

            if ($toRemove !== []) {
                foreach ($toRemove as $path) {
                    $dirName = dirname($path);

                    if (!isset($toRemoveDirs[$dirName])) {
                        $toRemoveDirs[$dirName] = 1;
                    }
                }

                dispatch(new DeleteFileJob(
                    path: $toRemove,
                    disk: $newPatternImage->getSaveToDiskName()
                ));
            }

            if ($toRemoveDirs !== []) {
                foreach (array_keys($toRemoveDirs) as $dir) {
                    dispatch(new DeleteDirectoryJob(
                        path: $dir,
                        disk: $newPatternImage->getSaveToDiskName()
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

            $newPatternFile = new PatternFile();

            if ($toRemove !== []) {
                foreach ($toRemove as $path) {
                    $dirName = dirname($path);

                    if (!isset($toRemoveDirs[$dirName])) {
                        $toRemoveDirs[$dirName] = 1;
                    }
                }

                dispatch(new DeleteFileJob(
                    path: $toRemove,
                    disk: $newPatternFile->getSaveToDiskName()
                ));
            }

            if ($toRemoveDirs !== []) {
                foreach (array_keys($toRemoveDirs) as $dir) {
                    dispatch(new DeleteDirectoryJob(
                        path: $dir,
                        disk: $newPatternFile->getSaveToDiskName()
                    ));
                }
            }
        };
    }

    protected function clearParsedPattern(Pattern &$pattern): void
    {
        dispatch(new RemoveFromPatternsMarkedForRemovalPatternAuthorsJob($pattern->id));

        dispatch(new RemoveFromPatternsMarkedForRemovalPatternCategoriesJob($pattern->id));

        dispatch(new RemoveFromPatternsMarkedForRemovalPatternTagsJob($pattern->id));

        dispatch(new ReplaceMarkedForReplacePatternAuthorsInPatternsJob($pattern->id));

        dispatch(new ReplaceMarkedForReplacePatternCategoriesInPatternsJob($pattern->id));

        dispatch(new ReplaceMarkedForReplacePatternTagsInPatternsJob($pattern->id));
    }
}
