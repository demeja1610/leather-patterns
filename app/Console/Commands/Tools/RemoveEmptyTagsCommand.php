<?php

namespace App\Console\Commands\Tools;

use App\Models\PatternTag;
use Illuminate\Console\Command;

class RemoveEmptyTagsCommand extends Command
{
    protected $signature = 'tools:remove-empty-tags';
    protected $description = 'Remove empty tags from patterns';

    public function handle()
    {
        $this->info('Removing empty tags...');

        $count = PatternTag::query()->whereDoesntHave('patterns')->delete();

        $this->info("Removed $count empty tags.");
    }
}
