<?php

declare(strict_types=1);

namespace App\Dto\MenuItem;

use App\Dto\ListDto;

class MenuItemListDto extends ListDto
{
    public function __construct(MenuItemDto ...$items)
    {
        parent::__construct(...$items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ...array_map(
                callback: MenuItemDto::fromArray(...),
                array: $data['items'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                callback: fn(MenuItemDto $item): array => $item->toArray(),
                array: $this->items,
            ),
        ];
    }

    /**
     * @return array<\App\Dto\MenuItem\MenuItemDto>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
