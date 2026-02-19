<?php

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedPatternCategoriesForPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-pattern-categories-for-patterns';
    protected $description = 'Replace marked with `replace_id` in DB pattern category for all patterns with pattern category with specified ID in `replace_id`';

    public function handle()
    {
        $q = Pattern::query()
            ->whereHas('categories', fn(Builder $query) => $query->whereNotNull('replace_id'))
            ->with('categories.replacement');

        $count = $q->count();

        $this->info("Total patterns found: {$count}");

        $result = 0;

        $q->chunkById(
            count: 250,
            callback: function (Collection $patterns) use (&$toCategory, &$fromCategory, &$result) {
                $pattern = $patterns->first();

                /**
                 * @var \App\Models\Pattern $pattern
                 */
                foreach ($patterns as $pattern) {
                    $categoriesForReplace = $pattern->categories
                        ->filter(fn(PatternCategory $patternCategory) => $patternCategory->replace_id !== null);

                    $categoriesForReplaceIds = $categoriesForReplace->pluck('id')->toArray();
                    $categoriesForReplaceNames = $categoriesForReplace->pluck('name')->implode('name', ', ');

                    $replacesIds = [];
                    $replacesNames = [];

                    foreach ($categoriesForReplace as $categoryForReplace) {
                        if (!$pattern->categories->contains('id', '=', $categoryForReplace->replacement->id)) {
                            $replacesIds[] = $categoryForReplace->replacement->id;
                            $replacesNames[] = $categoryForReplace->replacement->name;
                        } else {
                            $this->warn("Category with name: {$categoryForReplace->replacement->name} already attached to pattern");
                        }
                    }

                    $replacesNamesStr = implode(', ', $replacesNames);

                    $this->info("Detaching categories: `{$categoriesForReplaceNames}` from pattern ID: {$pattern->id}");

                    $pattern->categories()->detach($categoriesForReplaceIds);

                    if ($replacesIds === []) {
                        $this->warn("No categories to attach to pattern with ID: {$pattern->id}");

                        continue;
                    }

                    $this->info("Attaching categories: `{$replacesNamesStr}` from pattern ID: {$pattern->id}");

                    $pattern->categories()->attach($replacesIds);

                    $result++;
                }
            }
        );

        $this->info("Pattern categories replaced successfully, {$result} pattern records were affected.");
    }
}
