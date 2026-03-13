<?php

namespace App\Jobs\Parser\Pattern;

use Throwable;
use App\Models\Pattern;
use App\Models\PatternTag;
use App\Models\PatternCategory;
use App\Dto\Parser\Pattern\TagDto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Dto\Parser\Pattern\CategoryDto;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Dto\Parser\Pattern\ParsedPatternLinkDto;
use Illuminate\Support\Collection as SupportCollection;

class CreatePatternFromParsedPatternLinkJob implements ShouldQueue
{
    use Queueable;

    protected readonly string $actionName;

    public function __construct(
        public ParsedPatternLinkDto $patternLink,
    ) {
        $this->actionName = 'create pattern from parsed pattern link';
    }

    public function handle(): void
    {
        $this->logStart();

        $this->logPatternLink();

        $existingPattern = $this->getExistingPattern();

        if ($existingPattern instanceof Pattern) {
            $this->logPatternExists();

            return;
        }

        try {
            DB::beginTransaction();

            $pattern = $this->createPattern();

            $categories = $this->createCategories();

            $tags = $this->createTags();

            $this->attachCategoriesToPatterns($pattern, $categories);

            $this->attachTagsToPatterns($pattern, $tags);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logDbRollbackedBecauseOfError($th);
        }
    }

    protected function getExistingPattern(): ?Pattern
    {
        return Pattern::query()
            ->where('source_url', $this->patternLink->getSourceUrl())
            ->first();
    }

    protected function createPattern(): Pattern
    {
        $this->logCreatePattern();

        return Pattern::query()->create([
            'source' => $this->patternLink->getSource()->value,
            'source_url' => $this->patternLink->getSourceUrl(),
        ]);
    }

    protected function createCategories(): SupportCollection
    {
        $categories = [];

        if ($this->patternLink->getCategories()->isEmpty() !== true) {
            $this->logCreateCategories();

            foreach ($this->patternLink->getCategories()->getItems() as $category) {
                $categories[] = PatternCategory::query()->createOrFirst([
                    'name' => $category->getName(),
                ]);
            }
        }

        return collect($categories);
    }

    protected function createTags(): SupportCollection
    {
        $tags = [];

        if ($this->patternLink->getTags()->isEmpty() !== true) {
            $this->logCreateTags();

            foreach ($this->patternLink->getTags()->getItems() as $tag) {
                $tags[] = PatternTag::query()->createOrFirst([
                    'name' => $tag->getName(),
                ]);
            }
        }

        return collect($tags);
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternCategory>
     */
    protected function attachCategoriesToPatterns(Pattern &$pattern, SupportCollection &$categories): void
    {
        if ($categories->isEmpty() === false) {
            $this->logAttachCategoriesToPattern($pattern, $categories);

            $pattern->categories()->attach($categories);
        }
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternTag>
     */
    protected function attachTagsToPatterns(Pattern &$pattern, SupportCollection &$tags): void
    {
        if ($tags->isEmpty() === false) {
            $this->logAttachTagsToPattern($pattern, $tags);

            $pattern->tags()->attach($tags);
        }
    }

    protected function logStart(): void
    {
        Log::info("Start {$this->actionName}");
    }

    protected function logPatternLink(): void
    {
        Log::info(
            message: "Pattern link",
            context: [
                'pattern_link' => $this->patternLink->toArray(),
            ]
        );
    }

    protected function logPatternExists(): void
    {
        Log::info(
            message: "Pattern already exists",
            context: [
                'pattern_link' => $this->patternLink->toArray(),
            ]
        );
    }

    protected function logCreatePattern(): void
    {
        Log::info(
            message: "Create pattern",
            context: [
                'pattern_link' => $this->patternLink->toArray(),
            ]
        );
    }

    protected function logCreateCategories(): void
    {
        Log::info(
            message: "Create categories, if category already exists it will be ignored",
            context: [
                'categories' => array_map(
                    array: $this->patternLink->getCategories()->getItems(),
                    callback: fn(CategoryDto $category) => $category->toArray(),
                ),
            ]
        );
    }

    protected function logCreateTags(): void
    {
        Log::info(
            message: "Creating tags, if tag already exists it will be ignored",
            context: [
                'tags' => array_map(
                    array: $this->patternLink->getTags()->getItems(),
                    callback: fn(TagDto $tag) => $tag->toArray(),
                ),
            ],
        );
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternCategory> $categories
     */
    protected function logAttachCategoriesToPattern(Pattern &$pattern, SupportCollection &$categories): void
    {
        Log::info(
            message: "Attaching categories to pattern",
            context: [
                'categories' => $categories->toArray(),
                'pattern_id' => $pattern->id,
            ],
        );
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternTag> $tags
     */
    protected function logAttachTagsToPattern(Pattern &$pattern, SupportCollection &$tags): void
    {
        Log::info(
            message: "Attaching tags to pattern",
            context: [
                'tags' => $tags->toArray(),
                'pattern_id' => $pattern->id,
            ]
        );
    }

    protected function logDbRollbackedBecauseOfError(Throwable $th): void
    {
        Log::info(
            message: "An error happened while trying {$this->actionName}, all DB changes was rollbacked",
            context: [
                'error' => $th->__toString(),
            ],
        );
    }
}
