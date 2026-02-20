<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternImage;

use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteFullyAbsentPatternImagesCommand extends Command
{
    protected $signature = 'tools:pattern-image:delete-fully-absent';

    protected $description = 'Delete fully absent pattern images';

    public function handle(): void
    {
        $this->info(message: 'Deleting fully absent pattern images...');

        $deletedCount = 0;

        DB::table('pattern_images')
            ->chunkById(
                count: 500,
                callback: function (Collection $images) use (&$deletedCount): void {
                    $from = $images->first()->id;
                    $to = $images->last()->id;
                    $count = $images->count();

                    $this->info(message: "Checking pattern images from {$from} to {$to} (Total: {$count})");

                    $toDelete = [];

                    foreach ($images as $image) {
                        if (!Storage::disk('public')->exists($image->path)) {
                            $this->info(message: "Deleting fully absent pattern image with path: {$image->path}");

                            $toDelete[] = $image->id;
                        }
                    }

                    $deleted = DB::table('pattern_images')->whereIn(column: 'id', values: $toDelete)->delete();

                    $deletedCount += $deleted;
                }
            );

        $this->info(message: "Finished deleting fully absent pattern images.");

        $this->info(message: "Total deleted pattern images: {$deletedCount}");
    }
}
