<?php

namespace App\Dto\SessionNotification;

use App\Dto\ListDto;

class SessionNotificationListDto extends ListDto
{
    public function __construct(SessionNotificationDto ...$items)
    {
        return parent::__construct(...$items);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            ...array_map(
                array: $data['items'],
                callback: fn(array $item) => SessionNotificationDto::fromArray($item),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                array: $this->items,
                callback: fn(SessionNotificationDto $item) => $item->toArray(),
            ),
        ];
    }

    /**
     * @return array<\App\Dto\SessionNotification\SessionNotificationDto>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
