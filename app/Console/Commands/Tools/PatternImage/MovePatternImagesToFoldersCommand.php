<?php

namespace App\Console\Commands\Tools\PatternImage;

use App\Models\Pattern;
use App\Console\Commands\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class MovePatternImagesToFoldersCommand extends Command
{
    protected $signature = 'tools:pattern-image:move-to-folders';
    protected $description = 'Move individual pattern images to folders with pattern ids';

    public function handle()
    {
        $this->info('Starting procedure...');

        Pattern::query()
            ->orderBy('id')
            ->with([
                'images'
            ])
            ->chunk(
                count: 250,
                callback: function (Collection $chunk) {
                    $from = $chunk->first()->id;
                    $to = $chunk->last()->id;
                    $count = $chunk->count();

                    $this->info("Processing patterns  from {$from} to {$to} ({$count} total)...");

                    $case = 'CASE';
                    $ids = [];

                    foreach ($chunk as $pattern) {
                        $folderPath = "/images/patterns/{$pattern->id}/";

                        if (!Storage::disk('public')->exists($folderPath)) {
                            $this->info("Creating image directory for pattern with ID: {$pattern->id}");

                            Storage::disk('public')->makeDirectory($folderPath);
                        }

                        foreach ($pattern->images as $image) {
                            $ids[] = $image->id;

                            if (str_contains(trim($image->path, '/'), trim($folderPath, '/'))) {
                                continue;
                            }

                            $newImagePath = str_replace(
                                search: 'images/',
                                replace: trim($folderPath, '/') . '/',
                                subject: $image->path,
                            );

                            $case .= " WHEN id = {$image->id} THEN '{$newImagePath}'";

                            Storage::disk('public')
                                ->move(
                                    $image->path,
                                    $newImagePath,
                                );
                        }
                    }

                    if ($case === 'CASE') {
                        $this->info('Nothing to move, skipping chunk...');

                        return;
                    }

                    $case .= ' ELSE path END';

                    DB::table('pattern_images')->whereIn('id', $ids)->update([
                        'path' => DB::raw($case),
                    ]);
                },
            );
    }
}
