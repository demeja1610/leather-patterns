<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use Illuminate\Console\Command;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternTagsInPatternsJob;

class ReplaceMarkedForReplacePatternTagsInPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-for-replace-pattern-tags-in-patterns {--pattern_id=} {--tag_id=}';

    protected $description = 'Replace marked for replace pattern tag(s) in pattern(s)';

    public function handle(): void
    {
        $patternId = $this->option(key: 'pattern_id');
        $tagId = $this->option(key: 'tag_id');

        if ($patternId) {
            $this->info("Pattern ID is: {$patternId}");

            $patternId = (int) $patternId;
        }

        if ($tagId) {
            $this->info("Pattern tag ID is: {$tagId}");

            $tagId = (int) $tagId;
        }

        $this->info(
            "A job will be dispatched to replace marked for replace pattern tag(s) in pattern(s), don't forget to run the job"
        );

        ReplaceMarkedForReplacePatternTagsInPatternsJob::dispatch($patternId, $tagId);
    }
}
