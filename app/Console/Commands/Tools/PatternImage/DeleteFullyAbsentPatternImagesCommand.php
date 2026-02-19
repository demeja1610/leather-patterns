<?php

namespace App\Console\Commands\Tools\PatternImage;

use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteFullyAbsentPatternImagesCommand extends Command
{
    protected $signature = 'tools:pattern-image:delete-fully-absent';
    protected $description = 'Delete fully absent pattern images';

    public function handle()
    {
        $this->info('Deleting fully absent pattern images...');

        $deletedCount = 0;

        DB::table('pattern_images')
            ->chunkById(
                count: 500,
                callback: function (Collection $images) use (&$deletedCount) {
                    $from = $images->first()->id;
                    $to = $images->last()->id;
                    $count = $images->count();

                    $this->info("Checking pattern images from {$from} to {$to} (Total: {$count})");

                    $toDelete = [];

                    foreach ($images as $image) {
                        if (!Storage::disk('public')->exists($image->path)) {
                            $this->info("Deleting fully absent pattern image with path: {$image->path}");

                            $toDelete[] = $image->id;
                        }
                    }

                    $deleted = DB::table('pattern_images')->whereIn('id', $toDelete)->delete();

                    $deletedCount += $deleted;
                }
            );

        $this->info("Finished deleting fully absent pattern images.");

        $this->info("Total deleted pattern images: {$deletedCount}");
    }
}
