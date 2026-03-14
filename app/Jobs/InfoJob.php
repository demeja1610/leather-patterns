<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

abstract class InfoJob implements ShouldQueue
{
    use Queueable;

    public function info(string $message): void
    {
        echo "\033[36m{$message}\033[0m" . PHP_EOL;
    }

    public function error($message): void
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    public function warn($message): void
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    public function success($message): void
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }
}
