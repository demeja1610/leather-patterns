<?php

namespace App\Console\Commands\Tools\PatternImage;

use App\Console\Commands\Command;
use App\Jobs\PatternImage\ClearTempPatternImagesDirectoryJob;

class ClearTempPatternImagesDirectoryCommand extends Command
{
    protected $signature = 'tools:pattern-images:clear-temp-directory';
    protected $description = 'Removes all files in temporary pattern images directory';

    public function handle()
    {
        $this->info(message: "A job will be dispatched to remove pattern images in temporary directory, don't forget to run the job");

        ClearTempPatternImagesDirectoryJob::dispatch();
    }
}
