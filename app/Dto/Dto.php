<?php

declare(strict_types=1);

namespace App\Dto;

class Dto
{
    public static function fromArray(array $data): self
    {
        return new self();
    }

    public function toArray(): array
    {
        return get_object_vars(object: $this);
    }
}
