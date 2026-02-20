<?php

declare(strict_types=1);

namespace App\Console\Commands\Import;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportPatternVideosCommand extends Command
{
    protected $signature = 'import:pattern-videos';

    protected $description = 'Import pattern videos';

    public function handle(): void
    {
        $this->info(message: 'Importing pattern videos...');

        DB::connection('mysql_import')->table(table: 'pattern_video')
            ->join(table: 'videos', first: 'pattern_video.video_id', operator: '=', second: 'videos.id')
            ->join(table: 'patterns', first: 'pattern_video.pattern_id', operator: '=', second: 'patterns.id')
            ->where(column: 'patterns.source', operator: '!=', value: PatternSourceEnum::SKINCUTS->value)
            ->orderBy(column: 'pattern_video.video_id')
            ->select(columns: [
                'pattern_video.video_id as video_id',
                'pattern_video.pattern_id as pattern_id',
                'videos.url as video_url',
                'videos.from as video_from',
                'videos.identifier as video_identifier',
                'videos.created_at as video_created_at',
                'videos.updated_at as video_updated_at',
            ])
            ->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->video_id;
                    $to = $chunk->last()->video_id;
                    $count = $chunk->count();

                    $this->info(message: "Importing pattern videos from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    foreach ($chunk as $item) {
                        $toInsert[] = [
                            'pattern_id' => $item->pattern_id,
                            'url' => $item->video_url,
                            'source' => $item->video_from,
                            'source_identifier' => $item->video_identifier,
                            'created_at' => $item->video_created_at,
                            'updated_at' => $item->video_updated_at,
                        ];
                    }

                    DB::table('pattern_videos')->insert(values: $toInsert);
                }
            );

        $this->info(message: "All pattern videos imported successfully.");
    }
}
