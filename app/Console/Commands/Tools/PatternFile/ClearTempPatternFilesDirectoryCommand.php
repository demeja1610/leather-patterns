<?php

namespace App\Console\Commands\Tools\PatternFile;

use App\Console\Commands\Command;
use App\Jobs\PatternFile\ClearTempPatternFilesDirectoryJob;

class ClearTempPatternFilesDirectoryCommand extends Command
{
    protected $signature = 'tools:pattern-files:clear-temp-directory';
    protected $description = 'Removes all files in temporary pattern files directory';

    public function handle()
    {
        $this->info(message: "A job will be dispatched to remove pattern files in temporary directory, don't forget to run the job");

        ClearTempPatternFilesDirectoryJob::dispatch();
    }
}
