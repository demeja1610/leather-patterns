<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use Illuminate\Console\Command;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternCategoriesInPatternsJob;

class ReplaceMarkedForReplacePatternCategoriesInPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-for-replace-pattern-categories-in-patterns {--pattern_id=} {--category_id=}';

    protected $description = 'Replace marked for replace pattern category(s) in pattern(s)';

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
            "A job will be dispatched to replace marked for replace pattern category(s) in pattern(s), don't forget to run the job"
        );

        ReplaceMarkedForReplacePatternCategoriesInPatternsJob::dispatch($patternId, $categoryId);
    }
}
