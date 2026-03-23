<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;

class FileDto extends Dto
{
    public function __construct(
        protected readonly string $url,
        protected readonly array $postData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'],
            postData: $data['post_data'],
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'post_data' => $this->postData,
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getPostData(): array
    {
        return $this->postData;
    }
}
