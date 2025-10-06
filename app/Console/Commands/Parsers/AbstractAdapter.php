<?php

namespace App\Console\Commands\Parsers;

use App\Models\Pattern;
use App\Models\PatternTag;
use App\Models\PatternCategory;

abstract class AbstractAdapter
{
    protected array $tagsFilter = [];
    protected array $categoriesFilter = [];

    protected function getCategoriesFilterData(): array
    {
        if ($this->categoriesFilter === []) {
            $this->categoriesFilter = config('categories_swap_filter', []);
        }

        return $this->categoriesFilter;
    }

    protected function filterCategories(array $categories): array
    {
        $data = $this->getCategoriesFilterData();

        $result = [];

        foreach ($categories as $category) {
            $loweredCategory = mb_strtolower($category);

            if (!array_key_exists($loweredCategory, $data)) {
                $result[] = $category;

                continue;
            }

            if ($data[$loweredCategory] === null) {
                $this->warn("Category '{$category}' is filtered out.");

                continue;
            }

            $this->warn("Category '{$category}' is replaced with '{$data[$loweredCategory]}'.");

            $result[] = $data[$loweredCategory];
        }

        return $result;
    }

    protected function bindCategories(Pattern $pattern, array $categories): void
    {
        if (empty($categories)) {
            return;
        }

        $categoriesStr = implode(', ', $categories);

        $this->info("Binding categories ({$categoriesStr}) for pattern: {$pattern->id}");

        $_categories = [];

        $filteredCategories = $this->filterCategories($categories);

        foreach ($filteredCategories as $category) {
            if (trim($category) !== '') {
                $_categories[] = PatternCategory::query()->createOrFirst([
                    'name' => mb_ucfirst($category),
                ]);
            }
        }

        $pattern->categories()->sync($_categories);
    }

    protected function getTagsFilterData(): array
    {
        if ($this->tagsFilter === []) {
            $this->tagsFilter = config('tags_swap_filter', []);
        }

        return $this->tagsFilter;
    }

    protected function filterTags(array $tags): array
    {
        $data = $this->getTagsFilterData();

        $result = [];

        foreach ($tags as $tag) {
            $loweredTag = mb_strtolower($tag);

            if (!array_key_exists($loweredTag, $data)) {
                $result[] = $tag;

                continue;
            }

            if ($data[$loweredTag] === null) {
                $this->warn("Tag '{$tag}' is filtered out.");

                continue;
            }

            $this->warn("Tag '{$tag}' is replaced with '{$data[$loweredTag]}'.");

            $result[] = $data[$loweredTag];
        }

        return $result;
    }

    protected function bindTags(Pattern $pattern, array $tags): void
    {
        $tagsStr = implode(', ', $tags);

        $this->info("Binding tags ({$tagsStr}) for pattern: {$pattern->id}");

        $_tags = [];

        $filteredTags = $this->filterTags($tags);

        foreach ($filteredTags as $tag) {
            if (trim($tag) !== '') {
                $_tags[] = PatternTag::query()->createOrFirst([
                    'name' => mb_ucfirst($tag),
                ]);
            }
        }

        $pattern->tags()->sync($_tags);
    }

    public function info($message)
    {
        echo "\033[36m{$message}\033[0m" . PHP_EOL;
    }

    public function error($message)
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    public function warn($message)
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    public function success($message)
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }
}
