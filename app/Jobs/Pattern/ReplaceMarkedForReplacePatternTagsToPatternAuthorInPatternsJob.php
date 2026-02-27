<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedForReplacePatternTagsToPatternAuthorInPatternsJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'replace marked for replace pattern tag(s) to pattern author in pattern(s)';

    public function __construct(
        public ?int $patternId = null,
        public ?int $tagId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query()
            ->whereNull('author_id');

        $q->whereHas(relation: 'tags', callback: function (Builder $sq) {
            $sq->whereNotNull('replace_author_id');

            if ($this->tagId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern tag ID: {$this->tagId}");

                $sq->where('pattern_tag_id', $this->tagId);
            }

            return $sq;
        });

        $q->with(relations: 'tags.authorReplacement');

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
            ->filter(fn(PatternTag $patternTag): bool => $patternTag->replace_author_id !== null);

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
            /**
             * Author is belongsTo relationship
             * Take a look at other replace commands if for some reason this changes
             */
            $replacesIds[] = $tagForReplace->authorReplacement->id;
        }

        Log::info(
            ucfirst($this->actionName) .
                ". Detaching tags with IDs: {$tagsForReplaceIds->implode('id', ', ')} from pattern with id {$pattern->id}"
        );

        $pattern->tags()->detach(ids: $tagsForReplaceIds->toArray());

        if ($replacesIds === []) {
            Log::info(
                ucfirst($this->actionName) .
                    ". No authors to attach to pattern with ID: {$pattern->id}"
            );

            return;
        }

        if (count($replacesIds) > 1) {
            Log::info(
                ucfirst($this->actionName) .
                    ". Only one author is allowed, author with ID: `{$replacesIds[0]}` will be attached"
            );
        }

        Log::info(
            ucfirst($this->actionName) .
                ". Attaching author with ID: `{$replacesIds[0]}` to pattern ID: {$pattern->id}"
        );

        $pattern->update([
            'author_id' => $replacesIds[0],
        ]);
    }
}
