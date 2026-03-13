<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;
use App\Enum\PatternSourceEnum;

class ParsedPatternLinkDto extends Dto
{
    public function __construct(
        protected readonly PatternSourceEnum $source,
        protected readonly string $sourceUrl,
        protected readonly CategoryListDto $categories,
        protected readonly TagListDto $tags,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: PatternSourceEnum::from($data['source']),
            sourceUrl: $data['source_url'],
            categories: CategoryListDto::fromArray($data['categories']),
            tags: TagListDto::fromArray($data['tags']),
        );
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source->value,
            'source_url' => $this->sourceUrl,
            'categories' => $this->categories->toArray(),
            'tags' => $this->tags->toArray(),
        ];
    }

    public function getSource(): PatternSourceEnum
    {
        return $this->source;
    }

    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    public function getCategories(): CategoryListDto
    {
        return $this->categories;
    }

    public function getTags(): TagListDto
    {
        return $this->tags;
    }
}
