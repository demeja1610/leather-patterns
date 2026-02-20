<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternMeta;

use App\Models\Pattern;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneratePatternMetasCommand extends Command
{
    protected $signature = 'tools:pattern-meta:generate';

    protected $description = 'Generate pattern metas';

    public function handle(): void
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
            ->chunkById(500, function (Collection $patterns) use (&$created): void {
                $from = $patterns->first()->id;
                $to = $patterns->last()->id;
                $count = $patterns->count();

                $this->info("Processing patterns from {$from} to {$to} (Total: {$count})");

                $toInsert = [];

                foreach ($patterns as $pattern) {
                    $meta = [
                        'pattern_id' => $pattern->id,
                        'pattern_downloaded' => $pattern->files && $pattern->files->isNotEmpty(),
                        'images_downloaded' => $pattern->images && $pattern->images->isNotEmpty(),
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
