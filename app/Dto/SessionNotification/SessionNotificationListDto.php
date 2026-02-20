<?php

declare(strict_types=1);

namespace App\Dto\SessionNotification;

use App\Dto\ListDto;

class SessionNotificationListDto extends ListDto
{
    public function __construct(SessionNotificationDto ...$items)
    {
        parent::__construct(...$items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ...array_map(
                callback: SessionNotificationDto::fromArray(...),
                array: $data['items'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                callback: fn(SessionNotificationDto $item): array => $item->toArray(),
                array: $this->items,
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
