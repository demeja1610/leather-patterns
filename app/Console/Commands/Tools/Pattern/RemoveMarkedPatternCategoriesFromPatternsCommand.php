<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class RemoveMarkedPatternCategoriesFromPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:remove-marked-pattern-categories-from-patterns';

    protected $description = 'Remove marked as `remove_on_appear` in DB pattern category from all patterns with that category';

    public function handle(): void
    {

        $q = Pattern::query()
            ->whereHas(relation: 'categories', callback: fn($query) => $query->where(column: 'remove_on_appear', operator: true))
            ->with(relations: 'categories');

        $count = $q->count();

        $this->info(string: "Total patterns found: {$count} with category marked as `remove_on_appear` categories");

        $result = 0;

        $q->chunkById(
            count: 250,
            callback: function (Collection $patterns) use (&$result): void {
                /**
                 * @var \App\Models\Pattern $pattern
                 */
                foreach ($patterns as $pattern) {
                    $categoriesToRemove = $pattern->categories
                        ->filter(fn(PatternCategory $patternCategory): bool => (bool) $patternCategory->remove_on_appear);

                    $names = $categoriesToRemove->pluck('name')->implode('name', ', ');
                    $ids = $categoriesToRemove->pluck('id')->toArray();

                    $this->info(string: "Removing categories with names: {$names} from pattern with id {$pattern->id}");

                    $pattern->categories()->detach(ids: $ids);

                    $result++;
                }
            }
        );

        $this->info(string: "Pattern categories removed successfully, {$result} pattern records were affected.");
    }
}
