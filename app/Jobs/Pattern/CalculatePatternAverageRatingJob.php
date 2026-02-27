<?php

namespace App\Jobs\Pattern;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculatePatternAverageRatingJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?int $patternId = null
    ) {}

    public function handle(): void
    {
        Log::info('Start calculating patterns average rating');

        $q = $this->getBaseQuery();

        if ($this->patternId !== null) {
            $q->where('patterns.id', $this->patternId);
        }

        $i = 1;

        $q->chunkById(
            count: 250,
            callback: function (Collection $patterns) use (&$i): void {
                $case = 'CASE';
                $ids = [];
                $count = $patterns->count();

                Log::info("Calculating patterns average rating, processing chunk: {$i} containing: {$count} patterns");

                foreach ($patterns as $pattern) {
                    $case .= " WHEN id = {$pattern->id} THEN {$pattern->avg_rating}";

                    $ids[] = $pattern->id;
                }

                $case .= ' ELSE avg_rating END';

                DB::table('patterns')->whereIn(column: 'id', values: $ids)->update(values: [
                    'avg_rating' => DB::raw($case),
                ]);

                $i++;
            },
            column: 'patterns.id',
            alias: 'id',
        );
    }

    protected function getBaseQuery(): Builder
    {
        return DB::table('patterns')
            ->join(
                table: 'pattern_reviews',
                first: 'patterns.id',
                operator: '=',
                second: 'pattern_reviews.pattern_id',
            )
            ->where('pattern_reviews.is_approved', true)
            ->select(columns: [
                'patterns.id',
                DB::raw('AVG(pattern_reviews.rating) as avg_rating'),
            ])
            ->orderBy(column: 'patterns.id')
            ->groupBy('patterns.id');
    }
}
