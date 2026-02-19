<?php

namespace App\Console\Commands\Tools\PatternCategory;

use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UCFirstCategoriesCommand extends Command
{
    protected $signature = 'tools:pattern-category:ucfirst';
    protected $description = 'Perform ucfirst on pattern categories';

    public function handle()
    {
        $this->info('Performing ucfirst on pattern categories...');

        PatternCategory::query()
            ->chunkById(
                count: 500,
                callback: function (Collection $categories) {
                    foreach ($categories as $category) {
                        $this->info('Processing pattern category: ' . $category->name);

                        $category->name = mb_ucfirst($category->name);

                        $category->save();
                    }
                }
            );
    }
}
