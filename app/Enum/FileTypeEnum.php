<?php

declare(strict_types=1);

namespace App\Enum;

enum FileTypeEnum: string
{
    case IMAGE = 'image';
    case ARCHIVE = 'archive';
    case PDF = 'pdf';
    case DWG = 'dwg';
    case SWG = 'swg';

    public static function fromMimeType(string $mimeType): ?self
    {
        return match ($mimeType) {
            'image/jpeg' => self::IMAGE,
            'image/png' => self::IMAGE,
            'application/pdf' => self::PDF,
            'application/zip' => self::ARCHIVE,
            'application/x-rar' => self::ARCHIVE,
            'application/x-7z-compressed' => self::ARCHIVE,
            'image/vnd.dwg' => self::DWG,
            'image/svg+xml' => self::SWG,
            default => null,
        };
    }
}
