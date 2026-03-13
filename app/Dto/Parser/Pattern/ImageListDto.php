<?php

declare(strict_types=1);

namespace App\Dto\Parser\Pattern;

use App\Dto\ListDto;

class ImageListDto extends ListDto
{
    public function __construct(ImageDto ...$items)
    {
        parent::__construct(...$items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ...array_map(
                callback: ImageDto::fromArray(...),
                array: $data['items'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                callback: fn(ImageDto $item): array => $item->toArray(),
                array: $this->items,
            ),
        ];
    }

    /**
     * @return array<\App\Dto\Parser\Pattern\ImageDto>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
