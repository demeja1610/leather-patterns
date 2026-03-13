<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;

class FileDto extends Dto
{
    public function __construct(
        protected readonly string $url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'],
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
