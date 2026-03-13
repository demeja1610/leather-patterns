<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;
use App\Enum\VideoSourceEnum;

class VideoDto extends Dto
{
    public function __construct(
        protected readonly string $url,
        protected readonly VideoSourceEnum $source,
        protected readonly ?string $sourceIdentifier,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'],
            source: VideoSourceEnum::from($data['source']),
            sourceIdentifier: $data['source_identifier'],
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'source' => $this->source->value,
            'source_identifier' => $this->sourceIdentifier,
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSource(): VideoSourceEnum
    {
        return $this->source;
    }

    public function getSourceIdentifier(): ?string
    {
        return $this->sourceIdentifier;
    }
}
