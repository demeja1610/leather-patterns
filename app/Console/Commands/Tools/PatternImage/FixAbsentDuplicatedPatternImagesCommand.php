<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternImage;

use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FixAbsentDuplicatedPatternImagesCommand extends Command
{
    protected $signature = 'tools:pattern-image:fix-absent-duplicated';

    protected $description = 'Fix duplicated and absent pattern images';

    public function handle(): void
    {
        $this->info(message: 'Fixing duplicated and absent pattern images...');

        $restoredCount = 0;

        DB::table('pattern_images')
            ->select('hash', DB::raw('COUNT(*) as count'))
            ->groupBy('hash')
            ->havingRaw(sql: 'COUNT(*) > 1')
            ->orderBy(column: 'hash')
            ->chunk(
                count: 100,
                callback: function (Collection $chunk) use (&$restoredCount): void {
                    $count = $chunk->count();

                    $this->info(message: "Found {$count} duplicated pattern images:");

                    foreach ($chunk as $item) {
                        $this->info(message: "Processing duplicated pattern image with hash: {$item->hash}");

                        $images = DB::table('pattern_images')
                            ->where(column: 'hash', operator: $item->hash)
                            ->get();

                        $existingImagePath = null;
                        $notExistingImagePaths = [];

                        foreach ($images as $image) {
                            $this->info(message: "Checking existence of pattern image with path: {$image->path}");

                            if (Storage::disk('public')->exists($image->path)) {
                                $existingImagePath = $image->path;
                            } else {
                                $notExistingImagePaths[] = $image->path;
                            }
                        }

                        if ($notExistingImagePaths === []) {
                            $this->info(message: "All pattern images with hash {$item->hash} exist.");

                            continue;
                        }

                        $notExistingImagePathsCount = count(value: $notExistingImagePaths);

                        $this->info(message: "Found {$notExistingImagePathsCount} absent pattern images with hash {$item->hash}:");

                        if ($existingImagePath === null) {
                            $this->error(message: "No existing pattern image found for hash {$item->hash}, cannot proceed with restoration.");

                            continue;
                        }

                        foreach ($notExistingImagePaths as $absentPath) {
                            $this->info(message: "Restoring absent pattern image with path: {$absentPath}");

                            Storage::disk('public')->copy($existingImagePath, $absentPath);

                            $restoredCount++;
                        }
                    }
                }
            );

        $this->info(message: 'Finished fixing duplicated and absent pattern images.');

        $this->info(message: "Total restored pattern images: {$restoredCount}");
    }
}
