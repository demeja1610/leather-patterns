<?php

declare(strict_types=1);

namespace App\Dto;

use Traversable;
use ArrayIterator;
use IteratorAggregate;

class ListDto extends Dto implements IteratorAggregate
{
    protected array $items = [];

    public function __construct(...$items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data['items']);
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
        ];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array: $this->items);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function count(): int
    {
        return count(value: $this->items);
    }
}
