<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use Illuminate\Console\Command;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternTagsJob;

class RemoveFromPatternsMarkedForRemovalPatternTagsCommand extends Command
{
    protected $signature = 'tools:pattern:remove-from-patterns-marked-for-removal-pattern-tags {--pattern_id=} {--tag_id=}';

    protected $description = 'Remove marked for removal pattern tag(s) from pattern(s)';

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
            "A job will be dispatched to remove marked for removal pattern tag(s) from pattern(s), don't forget to run the job"
        );

        RemoveFromPatternsMarkedForRemovalPatternTagsJob::dispatch($patternId, $tagId);
    }
}
