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

    protected string $actionName = 'calculate pattern(s) average rating';

    public function __construct(
        public ?int $patternId = null
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = $this->getBaseQuery();

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Patterd ID: {$this->patternId}");

            $q->where('patterns.id', $this->patternId);

            $pattern = $q->first();

            $updated = 0;

            if ($pattern === null) {
                Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any reviews or don't exists");

                Log::info(ucfirst($this->actionName) . ". If pattern with ID: {$this->patternId} exists it's average rating will be set to zero");

                $updated =  DB::table('patterns')->where('id', $this->patternId)->update([
                    'avg_rating' => 0,
                ]);
            } else {
                $updated =  DB::table('patterns')->where('id', $this->patternId)->update([
                    'avg_rating' => $pattern->avg_rating,
                ]);
            }

            if ($updated > 0) {
                Log::info(ucfirst($this->actionName) . ". Average rating for pattern with ID: {$this->patternId} was updated");
            } else {
                Log::info(ucfirst($this->actionName) . ". No pattern was updated");
            }
        } else {
            $i = 1;

            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$i): void {
                    $case = 'CASE';
                    $ids = [];
                    $count = $patterns->count();

                    Log::info(ucfirst($this->actionName) . ", processing chunk: {$i} containing: {$count} patterns");

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

        Log::info("Finish {$this->actionName}");
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
