<?php

namespace App\Console\Commands\Tools\PatternImage;

use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FixAbsentDuplicatedPatternImagesCommand extends Command
{
    protected $signature = 'tools:pattern-image:fix-absent-duplicated';
    protected $description = 'Fix duplicated and absent pattern images';

    public function handle()
    {
        $this->info('Fixing duplicated and absent pattern images...');

        $restoredCount = 0;

        DB::table('pattern_images')
            ->select('hash', DB::raw('COUNT(*) as count'))
            ->groupBy('hash')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('hash')
            ->chunk(
                count: 100,
                callback: function (Collection $chunk) use (&$restoredCount) {
                    $count = $chunk->count();

                    $this->info("Found {$count} duplicated pattern images:");

                    foreach ($chunk as $item) {
                        $this->info("Processing duplicated pattern image with hash: {$item->hash}");

                        $images = DB::table('pattern_images')
                            ->where('hash', $item->hash)
                            ->get();

                        $existingImagePath = null;
                        $notExistingImagePaths = [];

                        foreach ($images as $image) {
                            $this->info("Checking existence of pattern image with path: {$image->path}");

                            if (Storage::disk('public')->exists($image->path)) {
                                $existingImagePath = $image->path;
                            } else {
                                $notExistingImagePaths[] = $image->path;
                            }
                        }

                        if ($notExistingImagePaths === []) {
                            $this->info("All pattern images with hash {$item->hash} exist.");

                            continue;
                        }

                        $notExistingImagePathsCount = count($notExistingImagePaths);

                        $this->info("Found {$notExistingImagePathsCount} absent pattern images with hash {$item->hash}:");

                        if ($existingImagePath === null) {
                            $this->error("No existing pattern image found for hash {$item->hash}, cannot proceed with restoration.");

                            continue;
                        }

                        foreach ($notExistingImagePaths as $absentPath) {
                            $this->info("Restoring absent pattern image with path: {$absentPath}");

                            Storage::disk('public')->copy($existingImagePath, $absentPath);

                            $restoredCount++;
                        }
                    }
                }
            );

        $this->info('Finished fixing duplicated and absent pattern images.');

        $this->info("Total restored pattern images: {$restoredCount}");
    }
}
