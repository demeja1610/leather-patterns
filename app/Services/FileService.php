<?php

namespace App\Services;

use App\Enum\FileTypeEnum;
use App\Interfaces\Services\FileServiceInterface;

class FileService implements FileServiceInterface
{
    public function getExtension(string $path): ?string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return $ext === ''
            ? null
            : $ext;
    }

    public function getFileType(string $mimeType): ?FileTypeEnum
    {
        return FileTypeEnum::fromMimeType($mimeType);
    }

    public function getSize(string $path): ?int
    {
        $size = filesize(filename: $path);

        return $size ? $size : null;
    }

    public function getMimeType(string $path): ?string
    {
        $finfo = finfo_open(flags: FILEINFO_MIME_TYPE);

        if (!$finfo) {
            return null;
        }

        $mimeType = finfo_file(finfo: $finfo, filename: $path);

        return $mimeType ? $mimeType : null;
    }

    public function getHashAlgo(): string
    {
        return 'sha256';
    }

    public function getHash(string $path): ?string
    {
        $hash = hash_file(algo: $this->getHashAlgo(), filename: $path);

        return $hash ? $hash : null;
    }

    public function generateName(string $prefix = ''): string
    {
        return uniqid(prefix: $prefix, more_entropy: true);
    }
}
