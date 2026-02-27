<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class RemoveFromPatternsMarkedForRemovalPatternTagsJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'remove from pattern(s) pattern tag(s) marked for removal';

    public function __construct(
        public ?int $patternId = null,
        public ?int $tagId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query();

        $q->whereHas(relation: 'tags', callback: function (Builder $sq) {
            $sq->where('remove_on_appear', true);

            if ($this->tagId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern tag ID: {$this->tagId}");

                $sq->where('pattern_tag_id', $this->tagId);
            }

            return $sq;
        });

        $q->with(relations: 'tags');

        $result = 0;

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('id', $this->patternId);

            $pattern = $q->first();

            if ($pattern === null) {
                Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any tags to remove or don't exists");

                return;
            }

            $this->removeMarkedTagsFromPattern(pattern: $pattern);

            $result++;
        } else {
            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$result): void {
                    /**
                     * @var \App\Models\Pattern $pattern
                     */
                    foreach ($patterns as $pattern) {
                        $this->removeMarkedTagsFromPattern(pattern: $pattern);

                        $result++;
                    }
                },
            );
        }

        Log::info("Finish {$this->actionName}, {$result} patterns was updated");
    }

    protected function removeMarkedTagsFromPattern(Pattern $pattern): void
    {
        $tagsToRemove = $pattern->tags
            ->filter(fn(PatternTag $patternTag): bool => (bool) $patternTag->remove_on_appear);

        if ($this->tagId !== null) {
            $tagsToRemove = $tagsToRemove
                ->filter(fn(PatternTag $patternTag): bool => $patternTag->id === $this->tagId);
        }

        if ($tagsToRemove->isEmpty()) {
            Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any tags to remove");

            return;
        }

        $ids = $tagsToRemove->pluck('id');

        Log::info(ucfirst($this->actionName) . ". Removing tags with IDs: {$ids->implode('id', ', ')} from pattern with id {$pattern->id}");

        $pattern->tags()->detach(ids: $ids->toArray());
    }
}
