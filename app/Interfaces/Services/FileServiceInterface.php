<?php

namespace App\Interfaces\Services;

interface FileServiceInterface
{
    public function getExtension(string $path): ?string;

    public function getSize(string $path): ?int;

    public function getMimeType(string $path): ?string;

    public function getHashAlgo(): string;

    public function getHash(string $path): ?string;
}
