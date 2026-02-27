<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Console\Commands\Command;
use App\Jobs\Pattern\CalculatePatternAverageRatingJob;

class CalculatePatternsAverageRating extends Command
{
    protected $signature = 'tools:pattern:calculate-average-rating {--pattern_id=}';

    protected $description = 'Calculate average rating for all patterns or for pattern specified with `pattern_id` option';

    public function handle(): void
    {
        $patternId = $this->option(key: 'pattern_id');

        $this->info(message: "A job will be dispatched to calculate pattern(s) average rating, don't forget to run the job");

        if ($patternId) {
            $this->info("Pattern ID is: {$patternId}");

            $patternId = (int) $patternId;
        }

        CalculatePatternAverageRatingJob::dispatch($patternId);
    }
}
