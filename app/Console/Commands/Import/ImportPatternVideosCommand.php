<?php

namespace App\Console\Commands\Import;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportPatternVideosCommand extends Command
{
    protected $signature = 'import:pattern-videos';
    protected $description = 'Import pattern videos';

    public function handle()
    {
        $this->info('Importing pattern videos...');

        DB::connection('mysql_import')->table('pattern_video')
            ->join('videos', 'pattern_video.video_id', '=', 'videos.id')
            ->join('patterns', 'pattern_video.pattern_id', '=', 'patterns.id')
            ->where('patterns.source', '!=', PatternSourceEnum::SKINCUTS->value)
            ->orderBy('pattern_video.video_id')
            ->select([
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
                callback: function (Collection $chunk) {
                    $from = $chunk->first()->video_id;
                    $to = $chunk->last()->video_id;
                    $count = $chunk->count();

                    $this->info("Importing pattern videos from {$from} to {$to} ({$count} total)...");

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

                    DB::table('pattern_videos')->insert($toInsert);
                }
            );

        $this->info("All pattern videos imported successfully.");
    }
}
