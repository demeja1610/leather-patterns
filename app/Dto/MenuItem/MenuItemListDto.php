<?php

namespace App\Dto\MenuItem;

use App\Dto\ListDto;

class MenuItemListDto extends ListDto {
    public function __construct(MenuItemDto ...$items)
    {
        return parent::__construct(...$items);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            ...array_map(
                array: $data['items'],
                callback: fn(array $item) => MenuItemDto::fromArray($item),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                array: $this->items,
                callback: fn(MenuItemDto $item) => $item->toArray(),
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
