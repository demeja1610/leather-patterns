<?php

declare(strict_types=1);

namespace App\Console\Commands\Import;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportPatternCategoriesCommand extends Command
{
    protected $signature = 'import:pattern-categories';

    protected $description = 'Import pattern categories';

    public function handle(): void
    {
        $this->info('Importing pattern categories...');

        DB::connection('mysql_import')->table('categories')
            ->orderBy('categories.id')
            ->select([
                'categories.id',
                'categories.name as categories_name',
                'categories.created_at as categories_created_at',
                'categories.updated_at as categories_updated_at',
            ])->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->id;
                    $to = $chunk->last()->id;
                    $count = $chunk->count();

                    $this->info("Importing pattern categories from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    foreach ($chunk as $item) {
                        $toInsert[] = [
                            'id' => $item->id,
                            'name' => $item->categories_name,
                            'created_at' => $item->categories_created_at,
                            'updated_at' => $item->categories_updated_at,
                        ];
                    }


                    DB::table('pattern_categories')->insertOrIgnore($toInsert);
                }
            );

        DB::connection('mysql_import')->table('category_pattern')
            ->join('categories', 'category_pattern.category_id', '=', 'categories.id')
            ->join('patterns', 'category_pattern.pattern_id', '=', 'patterns.id')
            ->where('patterns.source', '!=', PatternSourceEnum::SKINCUTS->value)
            ->orderBy('category_pattern.category_id')
            ->select([
                'category_pattern.category_id as category_id',
                'category_pattern.pattern_id as pattern_id',
            ])
            ->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->category_id;
                    $to = $chunk->last()->category_id;
                    $count = $chunk->count();

                    $this->info("Importing pattern categories from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    foreach ($chunk as $item) {

                        $toInsert[] = [
                            'pattern_category_id' => $item->category_id,
                            'pattern_id' => $item->pattern_id,
                        ];
                    }

                    DB::table('pattern_pattern_category')->insertOrIgnore($toInsert);
                }
            );

        $this->info('Pattern categories imported successfully.');
    }
}
