<?php

namespace App\Console\Commands;

use Illuminate\Console\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    public function info($message, $verbosity = null)
    {
        parent::info("\033[36m{$message}\033[0m", $verbosity);
    }

    public function error($message, $verbosity = null)
    {
        parent::info("\033[31m{$message}\033[0m", $verbosity);
    }

    public function warn($message, $verbosity = null)
    {
        parent::info("\033[33m{$message}\033[0m", $verbosity);
    }

    public function success($message, $verbosity = null)
    {
        parent::info("\033[32m{$message}\033[0m", $verbosity);
    }
}
