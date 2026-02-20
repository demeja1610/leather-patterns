<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedPatternTagsToPatternCategoryForPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-pattern-tags-to-pattern-category-for-patterns';

    protected $description = 'Replace marked with `replace_category_id` in DB pattern tag for all patterns with pattern category with specified ID in `replace_category_id`';

    public function handle(): void
    {
        $q = Pattern::query()
            ->whereHas(relation: 'tags', callback: fn(Builder $query) => $query->whereNotNull('replace_category_id'))
            ->with(
                relations: [
                    'categories',
                    'tags.categoryReplacement'
                ]
            );

        $count = $q->count();

        $this->info(string: "Total patterns found: {$count}");

        $result = 0;

        $q->chunkById(
            count: 250,
            callback: function (Collection $patterns) use (&$result): void {
                $pattern = $patterns->first();

                /**
                 * @var \App\Models\Pattern $pattern
                 */
                foreach ($patterns as $pattern) {
                    $tagsForReplace = $pattern->tags
                        ->filter(fn(PatternTag $patternTag): bool => $patternTag->replace_category_id !== null);

                    $tagsForReplaceIds = $tagsForReplace->pluck('id')->toArray();
                    $tagsForReplaceNames = $tagsForReplace->pluck('name')->implode('name', ', ');

                    $replacesIds = [];
                    $replacesNames = [];

                    foreach ($tagsForReplace as $tagForReplace) {
                        if (!$pattern->categories->contains('id', '=', $tagForReplace->categoryReplacement->id)) {
                            $replacesIds[] = $tagForReplace->categoryReplacement->id;
                            $replacesNames[] = $tagForReplace->categoryReplacement->name;
                        } else {
                            $this->warn(string: "Category with name: {$tagForReplace->categoryReplacement->name} already attached to pattern");
                        }
                    }

                    $replacesNamesStr = implode(separator: ', ', array: $replacesNames);

                    $this->info(string: "Detaching tags: `{$tagsForReplaceNames}` from pattern ID: {$pattern->id}");

                    $pattern->tags()->detach(ids: $tagsForReplaceIds);

                    if ($replacesIds === []) {
                        $this->warn(string: "No categories to attach to pattern with ID: {$pattern->id}");

                        continue;
                    }

                    $this->info(string: "Attaching categories: `{$replacesNamesStr}` from pattern ID: {$pattern->id}");

                    $pattern->categories()->attach(ids: $replacesIds);

                    $result++;
                }
            },
        );

        $this->info(string: "Pattern tags replaced successfully, {$result} pattern records were affected.");
    }
}
