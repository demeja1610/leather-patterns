<?php

namespace App\Console\Commands\Tools\Pattern;

use App\Console\Commands\Command;
use App\Jobs\Pattern\UnarchivePatternFilesJob;

class UnarchivePatternFilesCommand extends Command
{
    protected $signature = 'tools:unarchive-pattern-files {--pattern_id=} {--delete_original}';

    protected $description = 'Unarchive pattern(s) file(s) that is archive type';

    public function handle()
    {
        $patternId = $this->option(key: 'pattern_id');
        $deleteOriginal = $this->option('delete_original');

        $this->info(message: "A job will be dispatched to unarchive pattern(s) file(s), don't forget to run the job");

        if ($patternId) {
            $this->info("Pattern ID is: {$patternId}");

            $patternId = (int) $patternId;
        }

        UnarchivePatternFilesJob::dispatch($patternId, $deleteOriginal);
    }
}
