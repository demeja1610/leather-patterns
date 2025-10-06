<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools;

use App\Console\Commands\Command;
use Illuminate\Support\Facades\DB;

class CalculatePatternsAverageRating extends Command
{
    protected $signature = 'tools:calculate-patterns-average-rating {--id=}';

    protected $description = 'Calculate the average rating for all patterns';

    public function handle(): void
    {
        $this->info('Calculating patterns average rating...');

        $id = $this->option('id');

        $q = DB::table('patterns')
            ->join(
                table: 'pattern_reviews',
                first: 'patterns.id',
                operator: '=',
                second: 'pattern_reviews.pattern_id',
            )
            ->select([
                'patterns.id',
                DB::raw('AVG(pattern_reviews.rating) as avg_rating'),
            ])
            ->orderBy('patterns.id')
            ->groupBy('patterns.id');

        if ($id) {
            $q->where('patterns.id', $id);
        }

        $q->chunkById(
            count: 100,
            column: 'patterns.id',
            alias: 'id',
            callback: function ($patterns): void {
                $case = 'CASE';
                $ids = [];

                foreach ($patterns as $pattern) {
                    $case .= " WHEN id = {$pattern->id} THEN {$pattern->avg_rating}";
                    $ids[] = $pattern->id;
                }

                $case .= ' ELSE avg_rating END';

                DB::table('patterns')->whereIn('id', $ids)->update([
                    'avg_rating' => DB::raw($case),
                ]);
            },
        );
    }
}
