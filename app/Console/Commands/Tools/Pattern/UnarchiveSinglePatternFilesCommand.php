<?php

namespace App\Console\Commands\Tools\Pattern;

use App\Console\Commands\Command;
use App\Jobs\Pattern\UnarchiveSinglePatternFilesJob;

class UnarchiveSinglePatternFilesCommand extends Command
{
    protected $signature = 'tools:unarchive-single-pattern-files {--pattern_id=}';

    protected $description = 'Unarchive pattern(s) file(s) that is archive type and contains inside archive single file';

    public function handle()
    {
        $patternId = $this->option(key: 'pattern_id');

        $this->info(message: "A job will be dispatched to unarchive pattern(s) file(s), don't forget to run the job");

        if ($patternId) {
            $this->info("Pattern ID is: {$patternId}");

            $patternId = (int) $patternId;
        }

        UnarchiveSinglePatternFilesJob::dispatch($patternId);
    }
}
