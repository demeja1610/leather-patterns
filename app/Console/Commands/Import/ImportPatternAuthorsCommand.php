<?php

declare(strict_types=1);

namespace App\Console\Commands\Import;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportPatternAuthorsCommand extends Command
{
    protected $signature = 'import:pattern-authors';

    protected $description = 'Import pattern authors';

    public function handle(): void
    {
        $this->info(message: 'Importing pattern authors...');

        DB::connection('mysql_import')->table(table: 'authors')
            ->orderBy(column: 'authors.id')
            ->select(columns: [
                'authors.id',
                'authors.name as author_name',
                'authors.created_at as author_created_at',
                'authors.updated_at as author_updated_at',
            ])->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->id;
                    $to = $chunk->last()->id;
                    $count = $chunk->count();

                    $this->info(message: "Importing pattern authors from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    foreach ($chunk as $item) {
                        $toInsert[] = [
                            'id' => $item->id,
                            'name' => $item->author_name,
                            'created_at' => $item->author_created_at,
                            'updated_at' => $item->author_updated_at,
                        ];
                    }


                    DB::table('pattern_authors')->insertOrIgnore(values: $toInsert);
                },
            );

        DB::connection('mysql_import')->table(table: 'author_pattern')
            ->join(table: 'authors', first: 'author_pattern.author_id', operator: '=', second: 'authors.id')
            ->join(table: 'patterns', first: 'author_pattern.pattern_id', operator: '=', second: 'patterns.id')
            ->where(column: 'patterns.source', operator: '!=', value: PatternSourceEnum::SKINCUTS->value)
            ->orderBy(column: 'author_pattern.author_id')
            ->select(columns: [
                'author_pattern.author_id as author_id',
                'author_pattern.pattern_id as pattern_id',
            ])
            ->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->author_id;
                    $to = $chunk->last()->author_id;
                    $count = $chunk->count();

                    $this->info(message: "Importing pattern authors from {$from} to {$to} ({$count} total)...");

                    $case = "CASE";
                    $patternsIds = [];

                    foreach ($chunk as $item) {

                        $case .= " WHEN id = {$item->pattern_id} THEN '{$item->author_id}'";

                        $patternsIds[] = $item->pattern_id;
                    }

                    $case .= " ELSE author_id END";

                    $updated = DB::table('patterns')
                        ->whereIn(column: 'id', values: $patternsIds)
                        ->update(values: [
                            'author_id' => DB::raw($case),
                        ]);

                    $this->info(message: "Updated {$updated} patterns with new author IDs.");
                },
            );

        $this->info(message: "All pattern authors imported successfully.");
    }
}
