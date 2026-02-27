<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use Illuminate\Console\Command;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternCategorisJob;

class RemoveFromPatternsMarkedForRemovalPatternCategoriesCommand extends Command
{
    protected $signature = 'tools:remove-from-patterns-marked-for-removal-pattern-categories {--pattern_id=} {--category_id=}';

    protected $description = 'Remove marked for removal pattern category(s) from pattern(s)';

    public function handle(): void
    {
        $patternId = $this->option(key: 'pattern_id');
        $categoryId = $this->option(key: 'category_id');

        if ($patternId) {
            $this->info("Pattern ID is: {$patternId}");

            $patternId = (int) $patternId;
        }

        if ($categoryId) {
            $this->info("Pattern category ID is: {$categoryId}");

            $categoryId = (int) $categoryId;
        }

        $this->info(
            "A job will be dispatched to remove marked for removal pattern category(s) from pattern(s), don't forget to run the job"
        );

        RemoveFromPatternsMarkedForRemovalPatternCategorisJob::dispatch($patternId, $categoryId);
    }
}
