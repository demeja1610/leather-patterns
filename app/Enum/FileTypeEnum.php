<?php

declare(strict_types=1);

namespace App\Enum;

enum FileTypeEnum: string
{
    case IMAGE = 'image';
    case ARCHIVE = 'archive';
    case PDF = 'pdf';
    case DWG = 'dwg';
    case SVG = 'svg';

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
            'application/x-7z-compressed' => self::ARCHIVE,
            'application/x-tar' => self::ARCHIVE,
            'application/x-gzip' => self::ARCHIVE,
            'application/x-bzip2' => self::ARCHIVE,
            'application/x-xz' => self::ARCHIVE,
            'image/vnd.dwg' => self::DWG,
            'image/svg+xml' => self::SVG,
            default => null,
        };
    }
}
