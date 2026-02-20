<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedPatternTagsToPatternAuthorForPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-pattern-tags-to-pattern-author-for-patterns';

    protected $description = 'Replace marked with `replace_author_id` in DB pattern tag for all patterns with pattern author with specified ID in `replace_author_id`';

    public function handle(): void
    {
        $q = Pattern::query()
            ->whereNull('author_id')
            ->whereHas(relation: 'tags', callback: fn(Builder $query) => $query->whereNotNull('replace_author_id'))
            ->with(
                relations: [
                    'tags.authorReplacement'
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
                        ->filter(fn(PatternTag $patternTag): bool => $patternTag->replace_author_id !== null);

                    $tagsForReplaceIds = $tagsForReplace->pluck('id')->toArray();
                    $tagsForReplaceNames = $tagsForReplace->pluck('name')->implode('name', ', ');

                    $replacesIds = [];
                    $replacesNames = [];

                    foreach ($tagsForReplace as $tagForReplace) {
                        /**
                         * Author is belongsTo relationship
                         * Take a look at other replace commands if for some reason this changes
                         */
                        $replacesIds[] = $tagForReplace->authorReplacement->id;
                        $replacesNames[] = $tagForReplace->authorReplacement->name;
                    }

                    $this->info(string: "Detaching tags: `{$tagsForReplaceNames}` from pattern ID: {$pattern->id}");

                    $pattern->tags()->detach(ids: $tagsForReplaceIds);

                    if ($replacesIds === []) {
                        $this->warn(string: "No authors to attach to pattern with ID: {$pattern->id}");

                        continue;
                    }

                    if (count($replacesIds) > 1) {
                        $this->warn(string: "Only one author is allowed, author with name: `{$replacesNames[0]}` will be attached");
                    }

                    $this->info(string: "Attaching authors: `{$replacesNames[0]}` from pattern ID: {$pattern->id}");

                    $pattern->update([
                        'author_id' => $replacesIds[0],
                    ]);

                    $result++;
                }
            },
        );

        $this->info(string: "Pattern tags replaced successfully, {$result} pattern records were affected.");
    }
}
