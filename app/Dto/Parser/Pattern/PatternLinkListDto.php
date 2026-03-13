<?php

declare(strict_types=1);

namespace App\Dto\Parser\Pattern;

use App\Dto\ListDto;

class PatternLinkListDto extends ListDto
{
    public function __construct(ParsedPatternLinkDto ...$items)
    {
        parent::__construct(...$items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ...array_map(
                callback: ParsedPatternLinkDto::fromArray(...),
                array: $data['items'],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                callback: fn(ParsedPatternLinkDto $item): array => $item->toArray(),
                array: $this->items,
            ),
        ];
    }

    /**
     * @return array<\App\Dto\Parser\Pattern\ParsedPatternLinkDto>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
