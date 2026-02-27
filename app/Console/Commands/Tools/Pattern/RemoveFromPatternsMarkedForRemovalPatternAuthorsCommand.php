<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Console\Commands\Command;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternAuthorsJob;

class RemoveFromPatternsMarkedForRemovalPatternAuthorsCommand extends Command
{
    protected $signature = 'tools:pattern:remove-from-patterns-marked-for-removal-pattern-authors {--pattern_id=} {--author_id=}';

    protected $description = 'Remove marked for removal pattern author(s) from pattern(s)';

    public function handle(): void
    {
        $patternId = $this->option(key: 'pattern_id');
        $authorId = $this->option(key: 'author_id');

        if ($patternId) {
            $this->info("Pattern ID is: {$patternId}");

            $patternId = (int) $patternId;
        }

        if ($authorId) {
            $this->info("Pattern author ID is: {$authorId}");

            $authorId = (int) $authorId;
        }

        $this->info(
            "A job will be dispatched to remove marked for removal pattern author(s) from pattern(s), don't forget to run the job"
        );

        RemoveFromPatternsMarkedForRemovalPatternAuthorsJob::dispatch($patternId, $authorId);
    }
}
