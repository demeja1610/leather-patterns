<?php

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneratePatternMetasCommand extends Command
{
    protected $signature = 'tools:generate-pattern-metas';
    protected $description = 'Generate pattern metas';

    public function handle()
    {
        $this->info('Generating pattern metas...');

        $created = 0;

        Pattern::query()
            ->whereDoesntHave('meta')
            ->orderBy('id')
            ->with([
                'files',
                'images',
                'reviews',
            ])
            ->chunkById(500, function (Collection $patterns) use (&$created) {
                $from = $patterns->first()->id;
                $to = $patterns->last()->id;
                $count = $patterns->count();

                $this->info("Processing patterns from $from to $to (Total: $count)");

                $toInsert = [];

                foreach ($patterns as $pattern) {
                    $meta = [
                        'pattern_id' => $pattern->id,
                        'pattern_downloaded' => $pattern->files && $pattern->files->isNotEmpty()
                            ? true
                            : false,
                        'images_downloaded' => $pattern->images && $pattern->images->isNotEmpty()
                            ? true
                            : false,
                        'reviews_updated_at' => $pattern->reviews && $pattern->reviews->isNotEmpty()
                            ? $pattern->updated_at
                            : null,
                    ];

                    $toInsert[] = $meta;
                }

                if ($toInsert !== []) {
                    DB::table('pattern_metas')->insert($toInsert);

                    $this->info("Inserted " . count($toInsert) . " pattern metas");

                    $created += count($toInsert);
                }
            });
    }
}
