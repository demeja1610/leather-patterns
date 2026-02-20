<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class RemoveMarkedPatternTagsFromPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:remove-marked-pattern-tags-from-patterns';

    protected $description = 'Remove marked as `remove_on_appear` in DB pattern tag from all patterns with that tag';

    public function handle(): void
    {

        $q = Pattern::query()
            ->whereHas(relation: 'tags', callback: fn($query) => $query->where('remove_on_appear', true))
            ->with(relations: 'tags');

        $count = $q->count();

        $this->info(string: "Total patterns found: {$count} with tag marked as `remove_on_appear` tags");

        $result = 0;

        $q->chunkById(
            count: 250,
            callback: function (Collection $patterns) use (&$result): void {
                /**
                 * @var \App\Models\Pattern $pattern
                 */
                foreach ($patterns as $pattern) {
                    $tagsToRemove = $pattern->tags
                        ->filter(fn(PatternTag $patternTag): bool => (bool) $patternTag->remove_on_appear);

                    $names = $tagsToRemove->pluck('name')->implode('name', ', ');
                    $ids = $tagsToRemove->pluck('id')->toArray();

                    $this->info(string: "Removing tags with names: {$names} from pattern with id {$pattern->id}");

                    $pattern->tags()->detach(ids: $ids);

                    $result++;
                }
            },
        );

        $this->info(string: "Pattern tags removed successfully, {$result} pattern records were affected.");
    }
}
