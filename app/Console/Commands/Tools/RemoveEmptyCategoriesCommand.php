<?php

namespace App\Console\Commands\Tools;

use App\Models\PatternCategory;
use Illuminate\Console\Command;

class RemoveEmptyCategoriesCommand extends Command
{
    protected $signature = 'tools:remove-empty-categories';
    protected $description = 'Remove empty categories from patterns';

    public function handle()
    {
        $this->info('Removing empty categories...');

        $count = PatternCategory::query()->whereDoesntHave('patterns')->delete();

        $this->info("Removed $count empty categories.");
    }
}
