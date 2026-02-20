<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternFile;

use App\Models\Pattern;
use App\Console\Commands\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class MovePatternFilesToFoldersCommand extends Command
{
    protected $signature = 'tools:pattern-file:move-to-folders';

    protected $description = 'Move individual patterns to folders with pattern ids';

    public function handle(): void
    {
        $this->info(message: 'Starting procedure...');

        Pattern::query()
            ->orderBy('id')
            ->with(['files'])
            ->chunk(
                count: 250,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->id;
                    $to = $chunk->last()->id;
                    $count = $chunk->count();

                    $this->info(message: "Processing patterns  from {$from} to {$to} ({$count} total)...");

                    $case = 'CASE';
                    $ids = [];

                    foreach ($chunk as $pattern) {
                        $folderPath = "/patterns/{$pattern->id}/";

                        if (!Storage::disk('public')->exists($folderPath)) {
                            $this->info(message: "Creating directory for pattern with ID: {$pattern->id}");

                            Storage::disk('public')->makeDirectory($folderPath);
                        }

                        foreach ($pattern->files as $file) {
                            $ids[] = $file->id;

                            if (str_contains(haystack: trim(string: (string) $file->path, characters: '/'), needle: trim(string: $folderPath, characters: '/'))) {
                                continue;
                            }

                            $newPath = str_replace(
                                search: 'patterns/',
                                replace: trim(string: $folderPath, characters: '/') . '/',
                                subject: $file->path,
                            );

                            $case .= " WHEN id = {$file->id} THEN '{$newPath}'";

                            $this->info(message: "Moving file from {$file->path} to {$newPath}");

                            Storage::disk('public')
                                ->move(
                                    $file->path,
                                    $newPath,
                                );
                        }
                    }

                    if ($case === 'CASE') {
                        $this->info(message: 'Nothing to move, skipping chunk...');

                        return;
                    }

                    $case .= ' ELSE path END';

                    DB::table('pattern_files')->whereIn(column: 'id', values: $ids)->update(values: [
                        'path' => DB::raw($case),
                    ]);
                },
            );
    }
}
