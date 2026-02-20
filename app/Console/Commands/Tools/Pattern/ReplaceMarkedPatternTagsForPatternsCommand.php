<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedPatternTagsForPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-pattern-tags-for-patterns';

    protected $description = 'Replace marked with `replace_id` in DB pattern tag for all patterns with pattern tag with specified ID in `replace_id`';

    public function handle(): void
    {
        $q = Pattern::query()
            ->whereHas(relation: 'tags', callback: fn(Builder $query) => $query->whereNotNull('replace_id'))
            ->with(relations: 'tags.replacement');

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
                        ->filter(fn(PatternTag $patternTag): bool => $patternTag->replace_id !== null);

                    $tagsForReplaceIds = $tagsForReplace->pluck('id')->toArray();
                    $tagsForReplaceNames = $tagsForReplace->pluck('name')->implode('name', ', ');

                    $replacesIds = [];
                    $replacesNames = [];


                    foreach ($tagsForReplace as $tagForReplace) {
                        if (!$pattern->tags->contains('id', '=', $tagForReplace->replacement->id)) {
                            $replacesIds[] = $tagForReplace->replacement->id;
                            $replacesNames[] = $tagForReplace->replacement->name;
                        } else {
                            $this->warn(string: "Tag with name: {$tagForReplace->replacement->name} already attached to pattern");
                        }
                    }

                    $replacesNamesStr = implode(separator: ', ', array: $replacesNames);

                    $this->info(string: "Detaching tags: `{$tagsForReplaceNames}` from pattern ID: {$pattern->id}");

                    $pattern->tags()->detach(ids: $tagsForReplaceIds);

                    if ($replacesIds === []) {
                        $this->warn(string: "No tags to attach to pattern with ID: {$pattern->id}");

                        continue;
                    }

                    $this->info(string: "Attaching tags: `{$replacesNamesStr}` to pattern ID: {$pattern->id}");

                    $pattern->tags()->attach(ids: $replacesIds);

                    $result++;
                }
            },
        );

        $this->info(string: "Pattern tags replaced successfully, {$result} pattern records were affected.");
    }
}
