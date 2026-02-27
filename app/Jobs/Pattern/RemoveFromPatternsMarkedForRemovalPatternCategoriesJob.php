<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use App\Models\PatternCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class RemoveFromPatternsMarkedForRemovalPatternCategoriesJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'remove from pattern(s) pattern category(s) marked for removal';

    public function __construct(
        public ?int $patternId = null,
        public ?int $categoryId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query();

        $q->whereHas(relation: 'categories', callback: function (Builder $sq) {
            $sq->where('remove_on_appear', true);

            if ($this->categoryId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern category ID: {$this->categoryId}");

                $sq->where('pattern_category_id', $this->categoryId);
            }

            return $sq;
        });

        $q->with(relations: 'categories');

        $result = 0;

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('id', $this->patternId);

            $pattern = $q->first();

            if ($pattern === null) {
                Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any categories to remove or don't exists");

                return;
            }

            $this->removeMarkedCategoriesFromPattern(pattern: $pattern);

            $result++;
        } else {
            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$result): void {
                    /**
                     * @var \App\Models\Pattern $pattern
                     */
                    foreach ($patterns as $pattern) {
                        $this->removeMarkedCategoriesFromPattern(pattern: $pattern);

                        $result++;
                    }
                },
            );
        }

        Log::info("Finish {$this->actionName}, {$result} patterns was updated");
    }

    protected function removeMarkedCategoriesFromPattern(Pattern $pattern): void
    {
        $categoriesToRemove = $pattern->categories
            ->filter(fn(PatternCategory $patternCategory): bool => (bool) $patternCategory->remove_on_appear);

        if ($this->categoryId !== null) {
            $categoriesToRemove = $categoriesToRemove
                ->filter(fn(PatternCategory $patternCategory): bool => $patternCategory->id === $this->categoryId);
        }

        if ($categoriesToRemove->isEmpty()) {
            Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have any categories to remove");

            return;
        }

        $ids = $categoriesToRemove->pluck('id');

        Log::info(ucfirst($this->actionName) . ". Removing categories with IDs: {$ids->implode('id', ', ')} from pattern with id {$pattern->id}");

        $pattern->categories()->detach(ids: $ids->toArray());
    }
}
