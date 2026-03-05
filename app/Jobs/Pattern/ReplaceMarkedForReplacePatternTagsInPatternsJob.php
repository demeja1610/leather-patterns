<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedForReplacePatternTagsInPatternsJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'replace marked for replace pattern tag(s) in pattern(s)';

    public function __construct(
        public ?int $patternId = null,
        public ?int $tagId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query();

        $q->whereHas(relation: 'tags', callback: function (Builder $sq) {
            $sq->whereNotNull('replace_id')
                ->orWhereNotNull('replace_author_id')
                ->orWhereNotNull('replace_category_id');

            if ($this->tagId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern tag ID: {$this->tagId}");

                $sq->where('pattern_tag_id', $this->tagId);
            }

            return $sq;
        });

        $q->with(relations: [
            'tags.replacement',
            'tags.authorReplacement',
            'tags.categoryReplacement',
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
            ->filter(function (PatternTag $patternTag): bool {
                return $patternTag->replace_id !== null ||
                    $patternTag->replace_author_id !== null ||
                    $patternTag->replace_category_id !== null;
            });

        if ($this->tagId !== null) {
            $tagsForReplace = $tagsForReplace
                ->filter(fn(PatternTag $patternTag): bool => $patternTag->id === $this->tagId);
        }

        if ($tagsForReplace->isEmpty()) {
            Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any tag to replace");

            return;
        }

        try {
            DB::beginTransaction();

            $tagsForReplaceIds = $tagsForReplace->pluck('id');
            $replacesIds = [];
            $authorReplacesIds = [];
            $categoryReplacesIds = [];

            foreach ($tagsForReplace as $tagForReplace) {
                if ($tagForReplace->replace_id !== null) {
                    if (!$pattern->tags->contains('id', '=', $tagForReplace->replacement->id)) {
                        $replacesIds[] = $tagForReplace->replacement->id;
                    } else {
                        Log::info(
                            ucfirst($this->actionName) .
                                ". Pattern with id {$pattern->id} already has tag with ID: {$tagForReplace->replacement->id}"
                        );
                    }
                }

                if ($tagForReplace->replace_author_id !== null) {
                    if ($pattern->author_id === null) {
                        $authorReplacesIds[] = $tagForReplace->authorReplacement->id;
                    } else {
                        Log::info(
                            ucfirst($this->actionName) .
                                ". Pattern with id {$pattern->id} already has author. Author id: {$pattern->author_id}. It cannot be replaced to replace author for tag with ID: {$tagForReplace->replace_author_id}"
                        );
                    }
                }

                if ($tagForReplace->replace_category_id !== null) {
                    if (!$pattern->categories->contains('id', '=', $tagForReplace->categoryReplacement->id)) {
                        $categoryReplacesIds[] = $tagForReplace->categoryReplacement->id;
                    } else {
                        Log::info(
                            ucfirst($this->actionName) .
                                ". Pattern with id {$pattern->id} already has category with ID: {$tagForReplace->categoryReplacement->id}"
                        );
                    }
                }

                if ($replacesIds !== []) {
                    $replacesIdsStr = implode(
                        array: $replacesIds,
                        separator: ', ',
                    );

                    Log::info(
                        ucfirst($this->actionName) .
                            ". Attaching tags with IDs: `{$replacesIdsStr}` to pattern ID: {$pattern->id}"
                    );

                    $pattern->tags()->attach(ids: $replacesIds);
                } else {
                    Log::info(
                        ucfirst($this->actionName) .
                            ". No tags to attach to pattern with ID: {$pattern->id}"
                    );
                }

                if ($authorReplacesIds !== []) {
                    if (count($authorReplacesIds) > 1) {
                        Log::info(
                            ucfirst($this->actionName) .
                                ". Only one author is allowed, author with ID: `{$authorReplacesIds[0]}` will be attached"
                        );
                    }

                    Log::info(
                        ucfirst($this->actionName) .
                            ". Attaching author with ID: `{$authorReplacesIds[0]}` to pattern ID: {$pattern->id}"
                    );

                    $pattern->author_id = $authorReplacesIds[0];

                    $pattern->save();
                } else {
                    Log::info(
                        ucfirst($this->actionName) .
                            ". No authors to attach to pattern with ID: {$pattern->id}"
                    );
                }

                if ($categoryReplacesIds !== []) {
                    $categoryReplacesIdsStr = implode(
                        array: $categoryReplacesIds,
                        separator: ', ',
                    );

                    Log::info(
                        ucfirst($this->actionName) .
                            ". Attaching categories with IDs: `{$categoryReplacesIdsStr}` to pattern ID: {$pattern->id}"
                    );

                    $pattern->categories()->attach(ids: $categoryReplacesIds);
                } else {
                    Log::info(
                        ucfirst($this->actionName) .
                            ". No categories to attach to pattern with ID: {$pattern->id}"
                    );
                }

                Log::info(
                    ucfirst($this->actionName) .
                        ". Detaching tags with IDs: {$tagsForReplaceIds->implode('id', ', ')} from pattern with id {$pattern->id}"
                );

                $pattern->tags()->detach(ids: $tagsForReplaceIds->toArray());
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error(
                ucfirst($this->actionName) .
                    ". An error happened while trying to replace tags for pattern with ID: {$this->patternId}"
            );

            throw $th;
        }
    }
}
