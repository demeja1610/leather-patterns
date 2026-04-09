<?php

namespace App\Jobs\Pattern;

use App\Enum\FileTypeEnum;
use App\Interfaces\Services\FileServiceInterface;
use ZipArchive;
use App\Models\Pattern;
use App\Models\PatternFile;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UnarchiveSinglePatternFilesJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'unarchive pattern(s) single file(s)';

    protected FileServiceInterface $fileService;

    public function __construct(
        public ?int $patternId = null
    ) {}

    public function handle(FileServiceInterface $fileService): void
    {
        $this->fileService = $fileService;

        Log::info("Start {$this->actionName}");

        $q = $this->getBaseQuery();

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('patterns.id', $this->patternId);

            $pattern = $q->first();

            if ($pattern === null) {
                Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} doesn't have any files with .zip ext or doesn't exists");

                return;
            }

            $this->processPattern($pattern);
        } else {
            $i = 1;

            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$i): void {
                    $count = $patterns->count();

                    Log::info(ucfirst($this->actionName) . ", processing chunk: {$i} containing: {$count} patterns");

                    foreach ($patterns as $pattern) {
                        $this->processPattern($pattern);
                    }

                    $i++;
                },
                column: 'patterns.id',
                alias: 'id',
            );
        }

        Log::info("Finish {$this->actionName}");
    }

    protected function getBaseQuery(): Builder
    {
        $q =  Pattern::query()
            ->whereHas('files', fn(Builder $sq) => $sq->where('type', FileTypeEnum::ARCHIVE->value));

        $q->with([
            'files' => fn(HasMany $sq) => $sq->where('type', FileTypeEnum::ARCHIVE->value),
        ]);

        return $q;
    }

    protected function processPattern(Pattern &$pattern): void
    {
        Log::info(ucfirst($this->actionName) . " Processing pattern with ID: {$pattern->id}");

        foreach ($pattern->files as $file) {
            Log::info(ucfirst($this->actionName) . " Processing pattern file with ID: {$file->id} for pattern with ID: {$pattern->id}");

            $fileDiskName = $file->getSaveToDiskName();
            $filePath = Storage::disk($fileDiskName)->path($file->path);

            $filesCount = match ($file->extension) {
                'zip' => $this->countFilesInZipArchive($filePath),
                // 'rar' =>
                default => 0,
            };

            Log::info(ucfirst($this->actionName) . " Pattern file with ID: {$file->id} has {$filesCount} files inside");

            if ($filesCount > 1 || $filesCount === 0) {
                Log::info(ucfirst($this->actionName) .  "Skipping file with ID: {$file->id}");

                return;
            }

            $hasFolders = match ($file->extension) {
                'zip' => $this->isZipContainsFolders($filePath),
                // 'rar' =>
                default => false,
            };

            if ($hasFolders === true) {
                Log::info(ucfirst($this->actionName) . " Pattern file with ID: {$file->id} has folders inside, skipping.");

                return;
            }

            try {
                $newFiles = match ($file->extension) {
                    'zip', '7z' => $this->unzipFile($file),
                    default => [],
                };

                $newFilesCount = count($newFiles);

                Log::info(ucfirst($this->actionName) . "{$newFilesCount} files extracted from pattern file with ID: {$file->id}", [
                    'extracted_files' => $newFiles,
                ]);

                DB::beginTransaction();

                $savedCount = $this->saveNewFiles($file, $newFiles);

                if ($savedCount !== $newFilesCount) {
                    throw new Exception("Saved files count is not equals to exracted files count");
                }

                DB::commit();

                $file->delete();
            } catch (\Throwable $th) {
                DB::rollBack();

                Log::error(ucfirst($this->actionName) . " An error happened while trying to process file", [
                    'file_id' => $file->id,
                    'file_path' => $filePath,
                    'extraxted_files' => $newFiles,
                    'error' => $th->__toString(),

                ]);

                foreach ($newFiles as $file) {
                    Storage::disk($fileDiskName)->delete($newFiles);
                }
            }
        }
    }

    protected function countFilesInZipArchive(string $fullFilePath): int
    {
        $zip = new ZipArchive();
        $count = 0;

        if ($zip->open($fullFilePath) === true) {
            $count = $zip->count();
        } else {
            Log::error(ucfirst($this->actionName) . " Cannot open file", [
                'path' => $fullFilePath,
            ]);
        }

        $zip->close();

        return $count;
    }

    protected function isZipContainsFolders(string $fullFilePath): bool
    {
        $zip = new ZipArchive();

        $hasFolder = false;

        if ($zip->open($fullFilePath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                // A trailing slash indicates a directory entry
                if (substr($name, -1) === '/') {
                    $hasFolder = true;

                    break;
                }
            }
        } else {
            Log::error(ucfirst($this->actionName) . " Cannot open file", [
                'path' => $fullFilePath,
            ]);
        }

        $zip->close();

        return $hasFolder;
    }

    /**
     * @return array<string>
     */
    protected function unzipFile(PatternFile &$file): array
    {
        Log::info(ucfirst($this->actionName) . " Unzip pattern file with ID: {$file->id}");

        $zip = new ZipArchive();
        $newFiles = [];

        try {
            $fileDiskName = $file->getSaveToDiskName();

            $filePath = Storage::disk($fileDiskName)->path($file->path);

            if ($zip->open($filePath) === true) {
                $extractTofolderName = 'extracted_zip';
                $extractFromDir = trim(dirname($filePath), '/');
                $extractToDir =  $extractFromDir . "/{$extractTofolderName}";

                $zip->extractTo($extractToDir);

                $fileDirRelativePath = trim(dirname($file->path), '/');

                $extractedFiles = Storage::disk($fileDiskName)->files($fileDirRelativePath . "/{$extractTofolderName}");

                foreach ($extractedFiles as $extractedFile) {
                    $moveTo = $fileDirRelativePath . '/' . basename($extractedFile);

                    $successMove = Storage::disk($fileDiskName)->move(
                        from: $extractedFile,
                        to: $moveTo,
                    );

                    if ($successMove) {
                        $newFiles[] = $moveTo;
                    }
                }

                rmdir($extractToDir);
            }
        } catch (\Throwable $th) {
            Log::error(ucfirst($this->actionName) . " An error happened while trying to unzip file", [
                'file_id' => $file->id,
                'file_path' => $filePath,
                'error' => $th->__toString(),
            ]);

            foreach ($newFiles as $file) {
                Storage::disk($fileDiskName)->delete($newFiles);
            }

            Storage::disk($fileDiskName)->deleteDirectory($extractToDir);
        }

        $zip->close();

        return $newFiles;
    }

    protected function saveNewFiles(PatternFile &$file, array &$newFiles): int
    {
        $savedCount = 0;

        $fileDiskName = $file->getSaveToDiskName();

        foreach ($newFiles as $newFile) {
            $fullFilePath = Storage::disk($fileDiskName)->path($newFile);

            $mimeType = $this->fileService->getMimeType($fullFilePath);
            $ext = pathinfo($fullFilePath, PATHINFO_EXTENSION);

            PatternFile::query()->create([
                'hash' => $this->fileService->getHash($fullFilePath),
                'pattern_id' => $file->pattern_id,
                'extension' => $ext,
                'hash_algorithm' => $this->fileService->getHashAlgo(),
                'mime_type' => $mimeType,
                'path' => $newFile,
                'size' => $this->fileService->getSize($fullFilePath),
                'type' => FileTypeEnum::fromMimeType($mimeType),
            ]);

            $savedCount++;
        }

        return $savedCount;
    }
}
