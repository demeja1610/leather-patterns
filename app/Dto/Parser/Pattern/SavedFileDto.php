<?php

namespace App\Dto\Parser\Pattern;

use App\Dto\Dto;
use App\Enum\FileTypeEnum;

class SavedFileDto extends Dto
{
    public function __construct(
        protected readonly string $path,
        protected readonly string $ext,
        protected readonly int $size,
        protected readonly string $mime,
        protected readonly string $hashAlgorithm,
        protected readonly string $hash,
        protected readonly ?FileTypeEnum $type,
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
            type: $data['type'] ? FileTypeEnum::from($data['type']) : null,
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
            'type' => $this->type?->value,
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

    public function getType(): ?FileTypeEnum
    {
        return $this->type;
    }

    public function getSaveDiskName(): string
    {
        return $this->saveDiskName;
    }
}
