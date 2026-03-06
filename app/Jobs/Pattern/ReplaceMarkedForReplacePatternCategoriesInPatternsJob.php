<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use App\Models\PatternCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedForReplacePatternCategoriesInPatternsJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'replace marked for replace pattern category(s) in pattern(s)';

    public function __construct(
        public ?int $patternId = null,
        public ?int $categoryId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query();

        $q->whereHas(relation: 'categories', callback: function (Builder $sq) {
            $sq->whereNotNull('replace_id')
                ->orWhereNotNull('replace_tag_id');

            if ($this->categoryId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern category ID: {$this->categoryId}");

                $sq->where('pattern_category_id', $this->categoryId);
            }

            return $sq;
        });

        $q->with([
            'categories.replacement',
            'categories.tagReplacement',
        ]);

        $result = 0;

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('id', $this->patternId);

            $pattern = $q->first();

            if ($pattern === null) {
                Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any category to replace or don't exists");

                return;
            }

            $this->replaceMarkedCategoriesInPattern(pattern: $pattern);

            $result++;
        } else {
            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$result): void {
                    /**
                     * @var \App\Models\Pattern $pattern
                     */
                    foreach ($patterns as $pattern) {
                        $this->replaceMarkedCategoriesInPattern(pattern: $pattern);

                        $result++;
                    }
                },
            );
        }

        Log::info("Finish {$this->actionName}, {$result} patterns was updated");
    }

    protected function replaceMarkedCategoriesInPattern(Pattern $pattern): void
    {
        $categoriesForReplace = $pattern->categories
            ->filter(fn(PatternCategory $patternCategory): bool => $patternCategory->replace_id !== null || $patternCategory->replace_tag_id !== null);

        if ($this->categoryId !== null) {
            $categoriesForReplace = $categoriesForReplace
                ->filter(fn(PatternCategory $patternCategory): bool => $patternCategory->id === $this->categoryId);
        }

        if ($categoriesForReplace->isEmpty()) {
            Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any category to replace");

            return;
        }

        try {
            DB::beginTransaction();

            $categoriesForReplaceIds = $categoriesForReplace->pluck('id');
            $replacesIds = [];
            $tagReplacesIds = [];

            foreach ($categoriesForReplace as $categoryForReplace) {
                if ($categoryForReplace->replace_id !== null) {
                    if (!$pattern->categories->contains('id', '=', $categoryForReplace->replacement->id)) {
                        $replacesIds[] = $categoryForReplace->replacement->id;
                    } else {
                        Log::info(
                            ucfirst($this->actionName) .
                                ". Pattern with id {$pattern->id} already has category with ID: {$categoryForReplace->replacement->id}"
                        );
                    }
                }

                if ($categoryForReplace->replace_tag_id !== null) {
                    if (!$pattern->tags->contains('id', '=', $categoryForReplace->tagReplacement->id)) {
                        $tagReplacesIds[] = $categoryForReplace->tagReplacement->id;
                    } else {
                        Log::info(
                            ucfirst($this->actionName) .
                                ". Pattern with id {$pattern->id} already has tag with ID: {$categoryForReplace->tagReplacement->id}"
                        );
                    }
                }
            }

            if ($replacesIds !== []) {
                $replacesIdsStr = implode(
                    array: $replacesIds,
                    separator: ', ',
                );

                Log::info(
                    ucfirst($this->actionName) .
                        ". Attaching categories with IDs: `{$replacesIdsStr}` to pattern ID: {$pattern->id}"
                );

                $pattern->categories()->attach(ids: $replacesIds);
            } else {
                Log::info(
                    ucfirst($this->actionName) .
                        ". No categories to attach to pattern with ID: {$pattern->id}"
                );
            }

            if ($tagReplacesIds !== []) {
                $tagReplacesIdsStr = implode(
                    array: $tagReplacesIds,
                    separator: ', ',
                );

                Log::info(
                    ucfirst($this->actionName) .
                        ". Attaching tags with IDs: `{$tagReplacesIdsStr}` to pattern ID: {$pattern->id}"
                );

                $pattern->tags()->attach(ids: $tagReplacesIds);
            } else {
                Log::info(
                    ucfirst($this->actionName) .
                        ". No tags to attach to pattern with ID: {$pattern->id}"
                );
            }

            Log::info(
                ucfirst($this->actionName) .
                    ". Detaching categories with IDs: {$categoriesForReplaceIds->implode('id', ', ')} from pattern with id {$pattern->id}"
            );

            $pattern->categories()->detach(ids: $categoriesForReplaceIds->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error(
                ucfirst($this->actionName) .
                    ". An error happened while trying to replace category for pattern with ID: {$this->patternId}"
            );

            throw $th;
        }
    }
}
