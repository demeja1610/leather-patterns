<?php

namespace App\Jobs\Pattern;

use Exception;
use ZipArchive;
use App\Models\Pattern;
use App\Enum\FileTypeEnum;
use App\Models\PatternFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use App\Interfaces\Services\FileServiceInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnarchivePatternFilesJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'unarchive pattern(s) file(s)';

    protected FileServiceInterface $fileService;

    public function __construct(
        public ?int $patternId = null,
        public bool $deleteOriginal = false,
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
                Log::info(
                    ucfirst($this->actionName) .
                        ". Specified pattern with ID: {$this->patternId} doesn't have any files with type of 'archive' or doesn't exists"
                );

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
            Log::info(ucfirst($this->actionName) . " Processing pattern file with ID: {$file->id}", [
                'file' => $file->toArray(),
            ]);

            $fileDiskName = $file->getSaveToDiskName();
            $filePath = Storage::disk($fileDiskName)->path($file->path);

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

                if ($this->deleteOriginal === true) {
                    $file->delete();
                }
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
            $fileDirRelativePath = trim(dirname($file->path), '/');

            if ($zip->open($filePath) === true) {
                $extractToFolderName = 'extracted_zip';
                $extractFromDir = trim(dirname($filePath), '/');
                $extractToDir = $extractFromDir . "/{$extractToFolderName}";
                $extractToRelativeDir = $fileDirRelativePath . "/{$extractToFolderName}";

                if (!is_dir($extractToDir)) {
                    mkdir($extractToDir, 0777, true);
                }

                $extractedPaths = [];

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);

                    $originalName = $stat['name'];

                    if (substr($originalName, -1) === '/') {
                        continue;
                    }

                    $baseName = basename($originalName);

                    $uniqueName = uniqid() . '_' . $baseName;

                    $tempExtractPath = $extractToDir . '/' . $uniqueName;
                    $tempRelativeExtractPath = $extractToRelativeDir . '/' . $uniqueName;

                    if ($zip->extractTo($extractToDir, $originalName)) {
                        $originalExtractedPath = $extractToDir . '/' . $originalName;

                        if ($originalName !== $uniqueName && file_exists($originalExtractedPath)) {
                            rename($originalExtractedPath, $tempExtractPath);
                        }

                        $extractedPaths[] = $tempRelativeExtractPath;
                    }
                }

                foreach ($extractedPaths as $extractedFilePath) {

                    $fullExtractedFilePath = Storage::disk($fileDiskName)->path($extractedFilePath);

                    $fileMimeType = $this->fileService->getMimeType($fullExtractedFilePath);
                    $fileType = $this->fileService->getFileType($fileMimeType);

                    if ($fileType === null) {
                        Storage::disk($fileDiskName)->delete($extractedFilePath);

                        continue;
                    }

                    $moveTo = $fileDirRelativePath . '/' . basename($extractedFilePath);

                    $successMove = Storage::disk($fileDiskName)->move(
                        from: $extractedFilePath,
                        to: $moveTo,
                    );

                    if ($successMove) {
                        $newFiles[] = $moveTo;
                    }
                }

                $this->deleteDirectory($extractToDir);
            }
        } catch (\Throwable $th) {
            Log::error(ucfirst($this->actionName) . " An error happened while trying to unzip file", [
                'file_id' => $file->id,
                'file_path' => $filePath,
                'error' => $th->__toString(),
            ]);

            foreach ($newFiles as $deletedFile) {
                Storage::disk($fileDiskName)->delete($deletedFile);
            }

            if (isset($extractToDir) && is_dir($extractToDir)) {
                $this->deleteDirectory($extractToDir);
            }
        }

        $zip->close();

        return $newFiles;
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
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
                'parent_id' => $file->id,
            ]);

            $savedCount++;
        }

        return $savedCount;
    }
}
