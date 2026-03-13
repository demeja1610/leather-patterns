<?php

namespace App\Interfaces\Services;

use App\Enum\FileTypeEnum;

interface FileServiceInterface
{
    public function getExtension(string $path): ?string;

    public function getFileType(string $mimeType): ?FileTypeEnum;

    public function getSize(string $path): ?int;

    public function getMimeType(string $path): ?string;

    public function getHashAlgo(): string;

    public function getHash(string $path): ?string;

    public function generateName(string $prefix = ''): string;
}
