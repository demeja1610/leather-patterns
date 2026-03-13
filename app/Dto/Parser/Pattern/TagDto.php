<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;

class TagDto extends Dto
{
    public function __construct(
        protected readonly string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
