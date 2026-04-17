<?php

namespace App\Console\Commands\Tools\Pattern;

use App\Console\Commands\Command;
use App\Jobs\Pattern\DeleteUnarchivedPatternFilesJob;

class DeleteUnarchivedPatternFilesCommand extends Command
{
    protected $signature = 'tools:delete-unarchived-pattern-files {--pattern_id=}';

    protected $description = 'Unarchive pattern(s) file(s) that is archive type';

    public function handle()
    {
        $patternId = $this->option(key: 'pattern_id');

        $this->info(message: "A job will be dispatched to delete unarchived pattern(s) file(s), don't forget to run the job");

        if ($patternId) {
            $this->info("Pattern ID is: {$patternId}");

            $patternId = (int) $patternId;
        }

        DeleteUnarchivedPatternFilesJob::dispatch($patternId,);
    }
}
