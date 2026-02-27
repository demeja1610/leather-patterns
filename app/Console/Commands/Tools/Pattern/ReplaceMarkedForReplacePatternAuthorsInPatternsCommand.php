<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use Illuminate\Console\Command;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternAuthorsInPatternsJob;

class ReplaceMarkedForReplacePatternAuthorsInPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-for-replace-pattern-authors-in-patterns {--pattern_id=} {--author_id=}';

    protected $description = 'Replace marked for replace pattern author(s) in pattern(s)';

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
            "A job will be dispatched to replace marked for replace pattern author(s) in pattern(s), don't forget to run the job"
        );

        ReplaceMarkedForReplacePatternAuthorsInPatternsJob::dispatch($patternId, $authorId);
    }
}
