<?php

namespace App\Console\Commands\Tools;

use App\Models\PatternAuthor;
use Illuminate\Console\Command;

class RemoveEmptyAuthorsCommand extends Command
{
    protected $signature = 'tools:remove-empty-authors';
    protected $description = 'Remove empty authors from patterns';

    public function handle()
    {
        $this->info('Removing empty authors...');

        $count = PatternAuthor::query()->whereDoesntHave('patterns')->delete();

        $this->info("Removed $count empty authors.");
    }
}
