<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedForReplacePatternTagsToPatternCategoryInPatternsJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'replace marked for replace pattern tag(s) to pattern category in pattern(s)';

    public function __construct(
        public ?int $patternId = null,
        public ?int $tagId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query();

        $q->whereHas(relation: 'tags', callback: function (Builder $sq) {
            $sq->whereNotNull('replace_category_id');

            if ($this->tagId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern tag ID: {$this->tagId}");

                $sq->where('pattern_tag_id', $this->tagId);
            }

            return $sq;
        });

        $q->with(relations: [
            'categories',
            'tags.categoryReplacement'
        ]);

        $result = 0;

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('id', $this->patternId);

            $pattern = $q->first();

            if ($pattern === null) {
                Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any tag to replace or don't exists");

                return;
            }

            $this->replaceMarkedTagsInPattern(pattern: $pattern);

            $result++;
        } else {
            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$result): void {
                    /**
                     * @var \App\Models\Pattern $pattern
                     */
                    foreach ($patterns as $pattern) {
                        $this->replaceMarkedTagsInPattern(pattern: $pattern);

                        $result++;
                    }
                },
            );
        }

        Log::info("Finish {$this->actionName}, {$result} patterns was updated");
    }

    protected function replaceMarkedTagsInPattern(Pattern $pattern): void
    {
        $tagsForReplace = $pattern->tags
            ->filter(fn(PatternTag $patternTag): bool => $patternTag->replace_category_id !== null);

        if ($this->tagId !== null) {
            $tagsForReplace = $tagsForReplace
                ->filter(fn(PatternTag $patternTag): bool => $patternTag->id === $this->tagId);
        }

        if ($tagsForReplace->isEmpty()) {
            Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any tag to replace");

            return;
        }

        $tagsForReplaceIds = $tagsForReplace->pluck('id');
        $replacesIds = [];

        foreach ($tagsForReplace as $tagForReplace) {
            if (!$pattern->categories->contains('id', '=', $tagForReplace->categoryReplacement->id)) {
                $replacesIds[] = $tagForReplace->categoryReplacement->id;
            } else {
                Log::info(
                    ucfirst($this->actionName) .
                        ". Pattern with id {$pattern->id} already has category with ID: {$tagForReplace->categoryReplacement->id}"
                );
            }
        }

        Log::info(
            ucfirst($this->actionName) .
                ". Detaching tags with IDs: {$tagsForReplaceIds->implode('id', ', ')} from pattern with id {$pattern->id}"
        );

        $pattern->tags()->detach(ids: $tagsForReplaceIds->toArray());

        if ($replacesIds === []) {
            Log::info(
                ucfirst($this->actionName) .
                    ". No categories to attach to pattern with ID: {$pattern->id}"
            );

            return;
        }

        $replacesIdsStr = implode(
            array: $replacesIds,
            separator: ', ',
        );

        Log::info(
            ucfirst($this->actionName) .
                ". Attaching categories with IDs: `{$replacesIdsStr}` to pattern ID: {$pattern->id}"
        );

        $pattern->categories()->attach(ids: $replacesIds);
    }
}
