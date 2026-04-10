<?php

declare(strict_types=1);

namespace App\Enum;

enum FileTypeEnum: string
{
    case IMAGE = 'image';
    case ARCHIVE = 'archive';
    case PDF = 'pdf';
    case CAD = 'cad';
    case VECTOR = 'vector';

    public static function fromMimeType(string $mimeType): ?self
    {
        return match ($mimeType) {
            'image/jpeg' => self::IMAGE,
            'image/png' => self::IMAGE,
            'image/webp' => self::IMAGE,
            'image/bmp' => self::IMAGE,
            'application/pdf' => self::PDF,
            'application/zip' => self::ARCHIVE,
            'application/x-rar' => self::ARCHIVE,
            'application/vnd.rar' => self::ARCHIVE,
            'image/vnd.dwg' => self::CAD,
            'image/vnd.dxf' => self::CAD,
            'image/svg+xml' => self::VECTOR,
            default => null,
        };
    }
}
