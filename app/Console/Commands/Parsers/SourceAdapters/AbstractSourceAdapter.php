<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\SourceAdapters;

use App\Models\Pattern;
use App\Enum\PatternSourceEnum;
use App\Console\Commands\Parsers\AbstractAdapter;

abstract class AbstractSourceAdapter extends AbstractAdapter
{
    public function createNewPatterns(array $patterns): int
    {
        $urls = array_column(array: $patterns, column_key: 'source_url');

        $existingPatterns = Pattern::query()->whereIn('source_url', $urls)->get();

        $existingLinks = $existingPatterns->pluck(value: 'source_url')->toArray();

        $toCreate = $existingPatterns->count() === 0
            ? $patterns
            : array_filter(
                array: $patterns,
                callback: fn(array $pattern): bool => !in_array(
                    needle: $pattern['source_url'],
                    haystack: $existingLinks,
                ),
            );

        $this->info(message: "To create: " . count(value: $toCreate));

        $createdCount = 0;

        foreach ($toCreate as $pattern) {
            $categories = $pattern['categories'] ?? [];

            unset($pattern['categories']);

            $createdPattern = Pattern::query()->create(attributes: $pattern);

            if ($createdPattern->id !== null) {
                $createdCount++;

                if (!empty($categories)) {
                    $this->bindCategories(pattern: $createdPattern, categories: $categories);
                }
            }
        }

        return $createdCount;
    }

    protected function preparePatternForCreation(string $url, PatternSourceEnum $source, array $categories = []): array
    {
        return [
            'source_url' => $url,
            'source' => $source,
            'categories' => $categories,
        ];
    }
}
