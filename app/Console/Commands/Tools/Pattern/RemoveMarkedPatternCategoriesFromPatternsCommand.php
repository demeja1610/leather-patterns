<?php

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class RemoveMarkedPatternCategoriesFromPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:remove-marked-pattern-categories-from-patterns';
    protected $description = 'Remove marked as `remove_on_appear` in DB pattern category from all patterns with that category';

    public function handle()
    {

        $q = Pattern::query()
            ->whereHas('categories', fn($query) => $query->where('remove_on_appear', true))
            ->with('categories');

        $count = $q->count();

        $this->info("Total patterns found: {$count} with category marked as `remove_on_appear` categories");

        $result = 0;

        $q->chunkById(
            count: 250,
            callback: function (Collection $patterns) use (&$result) {
                /**
                 * @var \App\Models\Pattern $pattern
                 */
                foreach ($patterns as $pattern) {
                    $categoriesToRemove = $pattern->categories
                        ->filter(fn(PatternCategory $patternCategory) => (bool) $patternCategory->remove_on_appear === true);

                    $names = $categoriesToRemove->pluck('name')->implode('name', ', ');
                    $ids = $categoriesToRemove->pluck('id')->toArray();

                    $this->info("Removing categories with names: {$names} from pattern with id {$pattern->id}");

                    $pattern->categories()->detach($ids);

                    $result++;
                }
            }
        );

        $this->info("Pattern categories removed successfully, {$result} pattern records were affected.");
    }
}
