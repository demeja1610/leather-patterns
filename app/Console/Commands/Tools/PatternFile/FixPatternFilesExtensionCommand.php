<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternFile;

use App\Enum\FileTypeEnum;
use App\Models\PatternFile;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FixPatternFilesExtensionCommand extends Command
{
    protected $signature = 'tools:pattern-file:fix-extension {--id=}';

    protected $description = 'Fix pattern files extension';

    public function handle()
    {
        $id = $this->option(key: 'id');

        if ($id) {
            $file = PatternFile::query()->find(id: $id);

            if (!$file) {
                $this->error(string: "File not found");

                return static::FAILURE;
            }

            $this->fixFile(
                file: $file,
            );

            return static::SUCCESS;
        }

        $this->fixPdf();

        $this->fixZip();

        $this->fix7z();

        $this->fixRar();

        $this->fixJpeg();

        $this->fixPng();
    }

    protected function fixFile(PatternFile $file): void
    {
        $mimes = [
            'application/x-rar' => 'rar',
            'application/zip' => 'zip',
            'application/x-7z-compressed' => '7z',
            'application/x-tar' => 'tar',
            'application/x-gzip' => 'gz',
            'application/x-bzip2' => 'bz2',
            'application/x-xz' => 'xz',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'application/pdf' => 'pdf',
        ];

        $newExtension = $mimes[$file->mime_type] ?? null;

        if (!$newExtension) {
            $this->error(string: "Unknown MIME type: {$file->mime_type}");

            return;
        }

        $oldFilePath = $file->path;
        $newFilePath = str_replace(search: ".{$file->extension}", replace: ".{$newExtension}", subject: $oldFilePath);

        rename(
            from: public_path(path: "storage/{$oldFilePath}"),
            to: public_path(path: "storage/{$newFilePath}"),
        );

        $this->info(string: "Renamed: {$oldFilePath} to {$newFilePath}");

        $newHash = $this->calculateFileHash(filePath: $newFilePath);

        $file->update(attributes: [
            'extension' => $newExtension,
            'path' => $newFilePath,
            'hash' => $newHash,
            'type' => FileTypeEnum::fromMimeType(mimeType: $file->mime_type),
        ]);
    }

    protected function calculateFileHash(string $filePath): string
    {
        return hash_file(algo: 'sha256', filename: public_path(path: "storage/{$filePath}"));
    }

    protected function fixPdf(): void
    {
        $q = PatternFile::query()
            ->where(column: 'extension', operator: 'pdf')
            ->where(column: 'mime_type', operator: '!=', value: 'application/pdf');

        $wrongPdfCount = $q->count();

        $this->info(string: "Number of wrong PDF files: {$wrongPdfCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items): void {
                foreach ($items as $item) {
                    $this->fixFile(file: $item);
                }
            },
        );
    }

    protected function fixZip(): void
    {
        $q = PatternFile::query()
            ->where(column: 'extension', operator: 'zip')
            ->where(column: 'mime_type', operator: '!=', value: 'application/zip');

        $wrongZipCount = $q->count();

        $this->info(string: "Number of wrong ZIP files: {$wrongZipCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items): void {
                foreach ($items as $item) {
                    $this->fixFile(file: $item);
                }
            },
        );
    }

    protected function fix7z(): void
    {
        $q = PatternFile::query()
            ->where(column: 'extension', operator: '7z')
            ->where(column: 'mime_type', operator: '!=', value: 'application/x-7z-compressed');

        $wrong7zCount = $q->count();

        $this->info(string: "Number of wrong 7z files: {$wrong7zCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items): void {
                foreach ($items as $item) {
                    $this->fixFile(file: $item);
                }
            },
        );
    }

    protected function fixRar(): void
    {
        $q = PatternFile::query()
            ->where(column: 'extension', operator: 'rar')
            ->where(column: 'mime_type', operator: '!=', value: 'application/x-rar');

        $wrongRarCount = $q->count();

        $this->info(string: "Number of wrong RAR files: {$wrongRarCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items): void {
                foreach ($items as $item) {
                    $this->fixFile(file: $item);
                }
            },
        );
    }

    protected function fixJpeg(): void
    {
        $q = PatternFile::query()
            ->whereIn('extension', ['jpeg', 'jpg'])
            ->where(column: 'mime_type', operator: '!=', value: 'image/jpeg');

        $wrongJpegCount = $q->count();

        $this->info(string: "Number of wrong JPEG files: {$wrongJpegCount}");

        $q->orderBy(column: 'id')->chunkById(
            count: 100,
            callback: function (Collection $items): void {
                foreach ($items as $item) {
                    $this->fixFile(file: $item);
                }
            },
        );
    }

    protected function fixPng(): void
    {
        $q = PatternFile::query()
            ->where(column: 'extension', operator: 'png')
            ->where(column: 'mime_type', operator: '!=', value: 'image/png');

        $wrongPngCount = $q->count();

        $this->info(string: "Number of wrong PNG files: {$wrongPngCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items): void {
                foreach ($items as $item) {
                    $this->fixFile(file: $item);
                }
            },
        );
    }
}
