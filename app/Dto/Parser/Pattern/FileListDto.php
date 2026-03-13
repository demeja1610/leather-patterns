<?php

declare(strict_types=1);

namespace App\Dto\Parser\Pattern;

use App\Dto\ListDto;

class FileListDto extends ListDto
{
    public function __construct(FileDto ...$items)
    {
        parent::__construct(...$items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ...array_map(
                callback: FileDto::fromArray(...),
                array: $data['items'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                callback: fn(FileDto $item): array => $item->toArray(),
                array: $this->items,
            ),
        ];
    }

    /**
     * @return array<\App\Dto\Parser\Pattern\FileDto>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
