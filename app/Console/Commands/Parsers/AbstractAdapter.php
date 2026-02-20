<?php

declare(strict_types=1);

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
            $this->categoriesFilter = config(key: 'categories_swap_filter', default: []);
        }

        return $this->categoriesFilter;
    }

    protected function filterCategories(array $categories): array
    {
        $data = $this->getCategoriesFilterData();

        $result = [];

        foreach ($categories as $category) {
            $loweredCategory = mb_strtolower(string: (string) $category);

            if (!array_key_exists(key: $loweredCategory, array: $data)) {
                $result[] = $category;

                continue;
            }

            if ($data[$loweredCategory] === null) {
                $this->warn(message: "Category '{$category}' is filtered out.");

                continue;
            }

            $this->warn(message: "Category '{$category}' is replaced with '{$data[$loweredCategory]}'.");

            $result[] = $data[$loweredCategory];
        }

        return $result;
    }

    protected function bindCategories(Pattern $pattern, array $categories): void
    {
        if ($categories === []) {
            return;
        }

        $categoriesStr = implode(separator: ', ', array: $categories);

        $this->info(message: "Binding categories ({$categoriesStr}) for pattern: {$pattern->id}");

        $_categories = [];

        $filteredCategories = $this->filterCategories(categories: $categories);

        foreach ($filteredCategories as $category) {
            if (trim(string: (string) $category) !== '') {
                $_categories[] = PatternCategory::query()->createOrFirst(attributes: [
                    'name' => mb_ucfirst(string: $category),
                ]);
            }
        }

        $pattern->categories()->sync(ids: $_categories);
    }

    protected function getTagsFilterData(): array
    {
        if ($this->tagsFilter === []) {
            $this->tagsFilter = config(key: 'tags_swap_filter', default: []);
        }

        return $this->tagsFilter;
    }

    protected function filterTags(array $tags): array
    {
        $data = $this->getTagsFilterData();

        $result = [];

        foreach ($tags as $tag) {
            $loweredTag = mb_strtolower(string: (string) $tag);

            if (!array_key_exists(key: $loweredTag, array: $data)) {
                $result[] = $tag;

                continue;
            }

            if ($data[$loweredTag] === null) {
                $this->warn(message: "Tag '{$tag}' is filtered out.");

                continue;
            }

            $this->warn(message: "Tag '{$tag}' is replaced with '{$data[$loweredTag]}'.");

            $result[] = $data[$loweredTag];
        }

        return $result;
    }

    protected function bindTags(Pattern $pattern, array $tags): void
    {
        $tagsStr = implode(separator: ', ', array: $tags);

        $this->info(message: "Binding tags ({$tagsStr}) for pattern: {$pattern->id}");

        $_tags = [];

        $filteredTags = $this->filterTags(tags: $tags);

        foreach ($filteredTags as $tag) {
            if (trim(string: (string) $tag) !== '') {
                $_tags[] = PatternTag::query()->createOrFirst(attributes: [
                    'name' => mb_ucfirst(string: $tag),
                ]);
            }
        }

        $pattern->tags()->sync(ids: $_tags);
    }

    public function info($message): void
    {
        echo "\033[36m{$message}\033[0m" . PHP_EOL;
    }

    public function error($message): void
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    public function warn($message): void
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    public function success($message): void
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }
}
