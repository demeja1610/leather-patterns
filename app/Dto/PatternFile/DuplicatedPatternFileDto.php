<?php

namespace App\Dto\PatternFile;

use App\Dto\Dto;

class DuplicatedPatternFileDto extends Dto
{
    public function __construct(
        protected readonly string $hash,
        protected readonly int $duplicatesCount,
        protected readonly array $patternIds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hash: $data['hash'],
            duplicatesCount: $data['duplicates_count'],
            patternIds: $data['pattern_ids'],
        );
    }

    public function toArray(): array
    {
        return [
            'hash' => $this->hash,
            'duplicates_count' => $this->duplicatesCount,
            'pattern_ids' => $this->patternIds,
        ];
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getDuplicatesCount(): int
    {
        return $this->duplicatesCount;
    }

    /**
     * @return array<int>
     */
    public function getPatternIds(): array
    {
        return $this->patternIds;
    }
}
