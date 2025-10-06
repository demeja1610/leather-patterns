<?php

namespace App\Console\Commands\Tools;

use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UCFirstCategoriesCommand extends Command
{
    protected $signature = 'tools:ucfirst-categories';
    protected $description = 'Perform ucfirst on categories';

    public function handle()
    {
        $this->info('Performing ucfirst on categories...');

        PatternCategory::query()
            ->chunkById(
                count: 1,
                callback: function (Collection $categories) {
                    $category = $categories->first();

                    $this->info('Processing category: ' . $category->name);

                    $category->name = mb_ucfirst($category->name);

                    $category->save();
                }
            );
    }
}
