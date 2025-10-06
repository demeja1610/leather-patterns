<?php

namespace App\Console\Commands\Tools;

use App\Enum\FileTypeEnum;
use App\Models\PatternFile;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;

class FixPatternFilesExtensionCommand extends Command
{
    protected $signature = 'tools:fix-pattern-files-extension {--id=}';
    protected $description = 'Fix pattern files extension';

    public function handle()
    {
        $id = $this->option('id');

        if ($id) {
            $file = PatternFile::find($id);

            if (!$file) {
                $this->error("File not found");

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
            $this->error("Unknown MIME type: {$file->mime_type}");

            return;
        }

        $oldFilePath = $file->path;
        $newFilePath = str_replace(".{$file->extension}", ".{$newExtension}", $oldFilePath);

        rename(
            from: public_path("storage/{$oldFilePath}"),
            to: public_path("storage/{$newFilePath}")
        );

        $this->info("Renamed: {$oldFilePath} to {$newFilePath}");

        $newHash = $this->calculateFileHash($newFilePath);

        $file->update([
            'extension' => $newExtension,
            'path' => $newFilePath,
            'hash' => $newHash,
            'type' => FileTypeEnum::fromMimeType($file->mime_type)
        ]);
    }

    protected function calculateFileHash(string $filePath): string
    {
        return hash_file('sha256', public_path("storage/{$filePath}"));
    }

    protected function fixPdf(): void
    {
        $q = PatternFile::query()
            ->where('extension', 'pdf')
            ->where('mime_type', '!=', 'application/pdf');

        $wrongPdfCount = $q->count();

        $this->info("Number of wrong PDF files: {$wrongPdfCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items) {
                foreach ($items as $item) {
                    $this->fixFile($item);
                }
            }
        );
    }

    protected function fixZip(): void
    {
        $q = PatternFile::query()
            ->where('extension', 'zip')
            ->where('mime_type', '!=', 'application/zip');

        $wrongZipCount = $q->count();

        $this->info("Number of wrong ZIP files: {$wrongZipCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items) {
                foreach ($items as $item) {
                    $this->fixFile($item);
                }
            }
        );
    }

    protected function fix7z(): void
    {
        $q = PatternFile::query()
            ->where('extension', '7z')
            ->where('mime_type', '!=', 'application/x-7z-compressed');

        $wrong7zCount = $q->count();

        $this->info("Number of wrong 7z files: {$wrong7zCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items) {
                foreach ($items as $item) {
                    $this->fixFile($item);
                }
            }
        );
    }

    protected function fixRar(): void
    {
        $q = PatternFile::query()
            ->where('extension', 'rar')
            ->where('mime_type', '!=', 'application/x-rar');

        $wrongRarCount = $q->count();

        $this->info("Number of wrong RAR files: {$wrongRarCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items) {
                foreach ($items as $item) {
                    $this->fixFile($item);
                }
            }
        );
    }

    protected function fixJpeg(): void
    {
        $q = PatternFile::query()
            ->whereIn('extension', ['jpeg', 'jpg'])
            ->where('mime_type', '!=', 'image/jpeg');

        $wrongJpegCount = $q->count();

        $this->info("Number of wrong JPEG files: {$wrongJpegCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items) {
                foreach ($items as $item) {
                    $this->fixFile($item);
                }
            }
        );
    }

    protected function fixPng(): void
    {
        $q = PatternFile::query()
            ->where('extension', 'png')
            ->where('mime_type', '!=', 'image/png');

        $wrongPngCount = $q->count();

        $this->info("Number of wrong PNG files: {$wrongPngCount}");

        $q->orderBy('id')->chunkById(
            count: 100,
            callback: function (Collection $items) {
                foreach ($items as $item) {
                    $this->fixFile($item);
                }
            }
        );
    }
}
