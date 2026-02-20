<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternCategory;

use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UCFirstCategoriesCommand extends Command
{
    protected $signature = 'tools:pattern-category:ucfirst';

    protected $description = 'Perform ucfirst on pattern categories';

    public function handle(): void
    {
        $this->info(string: 'Performing ucfirst on pattern categories...');

        PatternCategory::query()
            ->chunkById(
                count: 500,
                callback: function (Collection $categories): void {
                    foreach ($categories as $category) {
                        $this->info(string: 'Processing pattern category: ' . $category->name);

                        $category->name = mb_ucfirst(string: $category->name);

                        $category->save();
                    }
                }
            );
    }
}
