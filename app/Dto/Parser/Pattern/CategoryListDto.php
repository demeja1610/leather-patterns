<?php

declare(strict_types=1);

namespace App\Dto\Parser\Pattern;

use App\Dto\ListDto;
use App\Dto\Parser\Pattern\CategoryDto;

class CategoryListDto extends ListDto
{
    public function __construct(CategoryDto ...$items)
    {
        parent::__construct(...$items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ...array_map(
                callback: CategoryDto::fromArray(...),
                array: $data['items'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                callback: fn(CategoryDto $item): array => $item->toArray(),
                array: $this->items,
            ),
        ];
    }

    /**
     * @return array<\App\Dto\Parser\Pattern\CategoryDto>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
