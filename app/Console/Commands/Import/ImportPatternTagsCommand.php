<?php

declare(strict_types=1);

namespace App\Console\Commands\Import;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportPatternTagsCommand extends Command
{
    protected $signature = 'import:pattern-tags';

    protected $description = 'Import pattern tags';

    public function handle(): void
    {
        $this->info(message: 'Importing pattern tags...');

        DB::connection('mysql_import')->table(table: 'tags')
            ->orderBy(column: 'tags.id')
            ->select(columns: [
                'tags.id',
                'tags.name as tags_name',
                'tags.created_at as tags_created_at',
                'tags.updated_at as tags_updated_at',
            ])->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->id;
                    $to = $chunk->last()->id;
                    $count = $chunk->count();

                    $this->info(message: "Importing pattern tags from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    foreach ($chunk as $item) {
                        $toInsert[] = [
                            'id' => $item->id,
                            'name' => $item->tags_name,
                            'created_at' => $item->tags_created_at,
                            'updated_at' => $item->tags_updated_at,
                        ];
                    }


                    DB::table('pattern_tags')->insertOrIgnore(values: $toInsert);
                }
            );

        DB::connection('mysql_import')->table(table: 'pattern_tag')
            ->join(table: 'tags', first: 'pattern_tag.tag_id', operator: '=', second: 'tags.id')
            ->join(table: 'patterns', first: 'pattern_tag.pattern_id', operator: '=', second: 'patterns.id')
            ->where(column: 'patterns.source', operator: '!=', value: PatternSourceEnum::SKINCUTS->value)
            ->orderBy(column: 'pattern_tag.tag_id')
            ->select(columns: [
                'pattern_tag.tag_id as tag_id',
                'pattern_tag.pattern_id as pattern_id',
            ])
            ->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->tag_id;
                    $to = $chunk->last()->tag_id;
                    $count = $chunk->count();

                    $this->info(message: "Importing pattern tags from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    foreach ($chunk as $item) {

                        $toInsert[] = [
                            'pattern_tag_id' => $item->tag_id,
                            'pattern_id' => $item->pattern_id,
                        ];
                    }

                    DB::table('pattern_pattern_tag')->insertOrIgnore(values: $toInsert);
                }
            );

        $this->info(message: 'Pattern tags imported successfully.');
    }
}
