<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;

class SavedImageDto extends Dto
{
    public function __construct(
        protected readonly string $path,
        protected readonly string $ext,
        protected readonly int $size,
        protected readonly string $mime,
        protected readonly string $hashAlgorithm,
        protected readonly string $hash,
        protected readonly string $saveDiskName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'],
            ext: $data['ext'],
            size: $data['size'],
            mime: $data['mime'],
            hashAlgorithm: $data['hash_algorithm'],
            hash: $data['hash'],
            saveDiskName: $data['save_disk_name'],
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'ext' => $this->ext,
            'size' => $this->size,
            'mime' => $this->mime,
            'hash_algorithm' => $this->hashAlgorithm,
            'hash' => $this->hash,
            'save_disk_name' => $this->saveDiskName,
        ];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getExt(): string
    {
        return $this->ext;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getHashAlgorithm(): string
    {
        return $this->hashAlgorithm;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getSaveDiskName(): string
    {
        return $this->saveDiskName;
    }
}
