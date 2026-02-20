<?php

declare(strict_types=1);

namespace App\Console\Commands\Import;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportPatternsCommand extends Command
{
    protected $signature = 'import:patterns';

    protected $description = 'Import patterns';

    public function handle(): void
    {
        $this->info('Importing patterns...');

        DB::connection('mysql_import')
            ->table('patterns')
            ->orderBy('id')
            ->select([
                'id',
                'title',
                'source',
                'source_url',
                'created_at',
                'updated_at',
            ])
            ->chunkById(
                count: 500,
                callback: function (Collection $patterns): void {
                    $from = $patterns->first()->id;
                    $to = $patterns->last()->id;
                    $count = $patterns->count();

                    $this->info("Imported patterns from {$from} to {$to} ({$count} total)");

                    $toInsert = [];

                    foreach ($patterns as $pattern) {
                        if ($pattern->source !== PatternSourceEnum::SKINCUTS->value) {
                            $toInsert[] = [
                                'id' => $pattern->id,
                                'title' => $pattern->title,
                                'source' => $pattern->source,
                                'source_url' => $pattern->source_url,
                                'created_at' => $pattern->created_at,
                                'updated_at' => $pattern->updated_at,
                            ];
                        }
                    }

                    DB::table('patterns')->insert($toInsert);
                }
            );

        $this->info("All patterns imported successfully.");
    }
}
