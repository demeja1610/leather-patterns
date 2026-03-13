<?php

namespace App\Jobs\Parser\Pattern;

use Throwable;
use GuzzleHttp\Client;
use App\Enum\FileTypeEnum;
use App\Models\PatternTag;
use App\Models\PatternFile;
use App\Models\PatternImage;
use App\Models\PatternCategory;
use App\Dto\Parser\Pattern\TagDto;
use Illuminate\Support\Facades\DB;
use App\Dto\Parser\Pattern\FileDto;
use Illuminate\Support\Facades\Log;
use App\Dto\Parser\Pattern\ImageDto;
use App\Dto\Parser\Pattern\CategoryDto;
use Illuminate\Support\Facades\Storage;
use App\Dto\Parser\Pattern\SavedFileDto;
use App\Dto\Parser\Pattern\SavedImageDto;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Dto\Parser\Pattern\ParsedPatternDto;
use App\Interfaces\Services\FileServiceInterface;
use Illuminate\Support\Collection as SupportCollection;

class UpdatePatternFromParsedPatternJob implements ShouldQueue
{
    use Queueable;

    protected readonly string $actionName;

    protected readonly Client $client;

    protected FileServiceInterface $fileService;

    public function __construct(
        public ParsedPatternDto $pattern,
    ) {
        $this->actionName = 'parse pattern data from source url';

        $this->client = new Client();
    }

    public function handle(FileServiceInterface $fileService): void
    {
        $this->fileService = $fileService;

        $this->logStart();

        $this->logPattern();

        $savedImages = [];

        $savedFiles = [];

        try {
            DB::beginTransaction();

            $this->updatePattern();

            $categories = $this->createCategories();

            $tags = $this->createTags();

            if ($this->pattern->getImages()->isEmpty() === false) {
                foreach ($this->pattern->getImages() as $image) {
                    $savedImage = $this->downloadImage($image);

                    if ($savedImage !== null) {
                        $savedImages[] = $savedImage;
                    }
                }
            }

            $images = $this->createImages(...$savedImages);

            if ($this->pattern->getFiles()->isEmpty() === false) {
                foreach ($this->pattern->getFiles() as $file) {
                    $savedFile = $this->downloadFile($file);

                    if ($savedFile !== null) {
                        if ($savedFile->getType() === null) {
                            $this->deleteSavedFiles($savedFile);
                        } else {
                            $savedFiles[] = $savedFile;
                        }
                    }
                }
            }

            $files = $this->createFiles(...$savedFiles);

            // // $this->logCreateVideos();

            // $videos = $this->createVideos();

            // // $this->logCreateReviews();

            // $reviews = $this->createReviews();

            // $this->logAttachCategoriesToPattern($categories);

            $this->attachCategories($categories);

            // // $this->logAttachTags($tags);

            $this->attachTags($tags);

            // // $this->logAttachImages($images)

            $this->attachImages($images);

            // // $this->logAttachFiles($files);

            $this->attachFiles($files);

            // // $this->loagAttachVideos($videos);

            // $this->attachVideos($videos);

            // // $this->logAttachReviews($reviews);

            // $this->attachReviews($reviews);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logDbRollbackedBecauseOfError($th);

            if ($savedImages !== []) {
                $this->deleteSavedImages(...$savedImages);
            }

            if ($savedFiles !== []) {
                $this->deleteSavedFiles(...$savedFiles);
            }
        }
    }

    protected function updatePattern(): bool
    {
        $pattern = $this->pattern->getPattern();

        $updated = false;

        if (trim($pattern->title) !== trim($this->pattern->getTitle())) {
            $pattern->title = $this->pattern->getTitle();
        }

        if ($pattern->isDirty()) {
            $this->logUpdatePattern();

            $updated = $pattern->save();
        }

        return $updated;
    }

    protected function createCategories(): SupportCollection
    {
        $categories = [];

        if ($this->pattern->getCategories()->isEmpty() === false) {
            $this->logCreateCategories();

            foreach ($this->pattern->getCategories()->getItems() as $category) {
                $categories[] = PatternCategory::query()->createOrFirst([
                    'name' => $category->getName(),
                ]);
            }
        }

        return collect($categories);
    }

    protected function createTags(): SupportCollection
    {
        $tags = [];

        if ($this->pattern->getTags()->isEmpty() === false) {
            $this->logCreateTags();

            foreach ($this->pattern->getTags()->getItems() as $tag) {
                $tags[] = PatternTag::query()->createOrFirst([
                    'name' => $tag->getName(),
                ]);
            }
        }

        return collect($tags);
    }

    protected function downloadImage(ImageDto &$image): ?SavedImageDto
    {
        $this->logDownloadImage($image);

        try {
            $response = $this->client->get($image->getUrl(), options: [
                'allow_redirects' => true,
            ]);

            $newPatternImage = new PatternImage();
            $saveDiskName = $newPatternImage->getSaveDiskName();
            $uploadPath = rtrim($newPatternImage->getUploadPath(), '/');

            $ext = $this->fileService->getExtension($image->getUrl()) ?? 'jpg';
            $newImageName = $this->fileService->generateName();

            $imagePath = "{$uploadPath}/{$newImageName}.{$ext}";

            $saved = Storage::disk($saveDiskName)->put(
                $imagePath,
                $response->getBody()->getContents(),
            );

            if (!$saved) {
                return null;
            }

            $fullImagePath = Storage::disk($saveDiskName)->path($imagePath);

            $savedImage = new SavedImageDto(
                path: $imagePath,
                ext: $ext,
                size: $this->fileService->getSize($fullImagePath),
                mime: $this->fileService->getMimeType($fullImagePath),
                hashAlgorithm: $this->fileService->getHashAlgo(),
                hash: $this->fileService->getHash($fullImagePath),
                saveDiskName: $saveDiskName,
            );

            $this->logSavedImage($image, $savedImage);

            return $savedImage;
        } catch (GuzzleException $e) {
            $this->logFailedToDownloadImage($image, $guzzleException);

            return null;
        }
    }

    protected function createImages(SavedImageDto ...$savedImages): SupportCollection
    {
        $images = [];

        if ($savedImages !== []) {
            $this->logCreateImages();

            foreach ($savedImages as $savedImage) {
                $images = PatternImage::query()->createOrFirst(
                    attributes: [
                        'hash' => $savedImage->getHash(),
                        'pattern_id' => $this->pattern->getPattern()->id,
                    ],
                    values: [
                        'extension' => $savedImage->getExt(),
                        'hash_algorithm' => $savedImage->getHashAlgorithm(),
                        'mime_type' => $savedImage->getMime(),
                        'path' => $savedImage->getPath(),
                        'size' => $savedImage->getSize(),
                    ],
                );
            }
        }

        return collect($images);
    }

    protected function downloadFile(FileDto &$file): ?SavedFileDto
    {
        $this->logDownloadFile($file);

        $url = match (true) {
            $this->isUrlYandexDisk($file->getUrl()) => $this->getDirectYandexDiskUrl($file->getUrl()),
            $this->isUrlGoogleDrive($file->getUrl()) => $this->getDirectGoogleDriveUrl($file->getUrl()),
            $this->isUrlVk($file->getUrl()) => $this->getDirectVkUrl($file->getUrl()),
            default => $file->getUrl(),
        };

        if ($url === null) {
            return null;
        }

        try {
            $params = [
                'allow_redirects' => true,
            ];

            $response = $this->client->get($url, $params);

            $newPatternFile = new PatternFile();
            $saveDiskName = $newPatternFile->getSaveDiskName();
            $uploadPath = rtrim($newPatternFile->getUploadPath(), '/');

            $ext = $this->fileService->getExtension($file->getUrl()) ?? 'pdf';
            $newFileName = $this->fileService->generateName();

            $filePath = "{$uploadPath}/{$newFileName}.{$ext}";

            $saved = Storage::disk($saveDiskName)->put(
                $filePath,
                $response->getBody()->getContents(),
            );

            if (!$saved) {
                return null;
            }

            $fullFilePath = Storage::disk($saveDiskName)->path($filePath);

            $mimeType = $this->fileService->getMimeType($fullFilePath);

            $savedFile = new SavedFileDto(
                path: $filePath,
                ext: $ext,
                size: $this->fileService->getSize($fullFilePath),
                mime: $mimeType,
                hashAlgorithm: $this->fileService->getHashAlgo(),
                hash: $this->fileService->getHash($fullFilePath),
                type: FileTypeEnum::fromMimeType($mimeType),
                saveDiskName: $saveDiskName,
            );

            $this->logSavedFile($file, $savedFile);

            $savedFile = $this->fixSavedFileExt($savedFile);

            return $savedFile;
        } catch (GuzzleException $guzzleException) {
            $this->logFailedToDownloadFile($file, $guzzleException);

            return null;
        }
    }

    protected function createFiles(SavedFileDto ...$savedFiles): SupportCollection
    {
        $files = [];

        if ($savedFiles !== []) {
            $this->logCreateFiles();

            foreach ($savedFiles as $savedFile) {
                $files = PatternFile::query()->createOrFirst(
                    attributes: [
                        'hash' => $savedFile->getHash(),
                        'pattern_id' => $this->pattern->getPattern()->id,
                    ],
                    values: [
                        'extension' => $savedFile->getExt(),
                        'hash_algorithm' => $savedFile->getHashAlgorithm(),
                        'mime_type' => $savedFile->getMime(),
                        'path' => $savedFile->getPath(),
                        'size' => $savedFile->getSize(),
                        'type' => $savedFile->getType(),
                    ],
                );
            }
        }

        return collect($files);
    }

    protected function isUrlYandexDisk(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return str_contains($url, 'yadi.sk') || str_contains($url, 'disk.yandex');
    }

    protected function isUrlGoogleDrive(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return str_contains($url, 'drive.google.com');
    }

    protected function isUrlVk(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return str_contains($url, 'vk.com');
    }

    protected function getDirectYandexDiskUrl(string $url): ?string
    {
        $this->logGettingDirectYandexDiskUrl($url);

        try {
            $response = $this->client->get(
                uri: "https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key={$url}",
            );
        } catch (Throwable $th) {
            $this->logFailToGetDirectYandexDiskUrl($url, $th);

            return null;
        }

        $body = $response->getBody()->getContents();

        $json = json_decode(json: $body, associative: true);

        if (!isset($json['href'])) {
            $this->logFailToGetDirectYandexDiskUrl($url);

            return null;
        }

        return $json['href'];
    }

    protected function getDirectGoogleDriveUrl(string $url): ?string
    {
        $this->logGettingDirectGoogleDriveUrl($url);

        $fileId = null;

        preg_match(pattern: '/\/d\/([a-zA-Z0-9_-]+)/', subject: $url, matches: $matches);

        if (isset($matches[1])) {
            $fileId = $matches[1];
        }

        if (!$fileId === null) {
            $this->logFailToGetDirectGoogleDriveUrl($url);
        }

        return $fileId
            ? "https://drive.google.com/uc?export=download&id={$fileId}"
            : null;
    }

    protected function getDirectVkUrl(string $url): ?string
    {
        $this->logGettingDirectVkUrl($url);

        $this->logFailToGetDirectVkUrl($url);

        return null;
    }

    protected function fixSavedFileExt(SavedFileDto &$savedFile): SavedFileDto
    {
        $this->logCheckingSavedFileMimeType($savedFile);

        $mimeExt = $this->getSavedFileExtBasedOnMimeType($savedFile);

        if ($savedFile->getExt() !== $mimeExt) {
            $this->logExtMimeMismatch($savedFile, $realExt);

            if ($mimeExt === null) {
                return SavedFileDto::fromArray(
                    array_merge(
                        ...$savedFile->toArray(),
                        [
                            'type' => null,
                        ],
                    ),
                );
            }

            $newPath = str_replace(
                search: ".{$savedFile->getExt()}",
                replace: ".{$mimeExt}",
                subject: $savedFile->getPath()
            );

            $moved =  Storage::disk($savedFile->getSaveDiskName())->move(
                from: $savedFile->getPath(),
                to: $newPath,
            );

            if ($moved === true) {
                $newSavedFile = SavedFileDto::fromArray(
                    array_merge(
                        ...$savedFile->toArray(),
                        [
                            'type' => FileTypeEnum::fromMimeType($savedFile->getMime()),
                            'ext' => $savedFile->getExt(),
                        ],
                    ),
                );

                $this->logSavedFileWasMoved($savedFile, $newSavedFile);
            }
        }

        return $savedFile;
    }

    protected function getSavedFileExtBasedOnMimeType(SavedFileDto &$savedFile): ?string
    {
        return match ($savedFile->getMime()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'application/x-rar' => 'rar',
            'application/x-7z-compressed' => '7z',
            'application/x-tar' => 'tar',
            'application/x-gzip' => 'gz',
            'application/x-bzip2' => 'bz2',
            'application/x-xz' => 'xz',
            'image/vnd.dwg' => 'dwg',
            'image/svg+xml' => 'svg',
            default => null,
        };
    }










    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternCategory>
     */
    protected function attachCategories(SupportCollection &$categories): void
    {
        if ($categories->isEmpty() === false) {
            $this->logAttachCategories($categories);

            $this->pattern->getPattern()->categories()->attach($categories);
        }
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternTag>
     */
    protected function attachTags(SupportCollection &$tags): void
    {
        if ($tags->isEmpty() === false) {
            $this->logAttachTags($tags);

            $this->pattern->getPattern()->tags()->attach($tags);
        }
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternImage> $images
     */
    protected function attachImages(SupportCollection &$images): void
    {
        if ($images->isEmpty() === false) {
            $this->logAttachImages($images);

            $this->pattern->getPattern()->images()->saveMany($images);
        }
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternFile> $files
     */
    protected function attachFiles(SupportCollection &$files): void
    {
        if ($files->isEmpty() === false) {
            $this->logAttachFiles($files);

            $this->pattern->getPattern()->files()->saveMany($files);
        }
    }






    protected function deleteSavedImages(SavedImageDto ...$savedImages): void
    {
        if ($savedImages !== []) {
            foreach ($savedImages as $savedImage) {
                if (Storage::disk($savedImage->getSaveDiskName())->exists($savedImage->getPath())) {
                    $this->logDeleteSavedImage($savedImage);

                    Storage::disk($savedImage->getSaveDiskName())->delete($savedImage->getPath());
                }
            }
        }
    }

    protected function deleteSavedFiles(SavedFileDto ...$savedFiles): void
    {
        if ($savedFiles !== []) {
            foreach ($savedFiles as $savedFile) {
                if (Storage::disk($savedFile->getSaveDiskName())->exists($savedFile->getPath())) {
                    $this->logDeleteSavedFile($savedFile);

                    Storage::disk($savedFile->getSaveDiskName())->delete($savedFile->getPath());
                }
            }
        }
    }




    protected function logStart(): void
    {
        Log::info("Start {$this->actionName}");
    }

    protected function logPattern(): void
    {
        Log::info(
            message: "Pattern URL: {$this->pattern->getPattern()->source_url}",
            context: [
                'data' => $this->pattern->toArray(),
            ],
        );
    }

    protected function logUpdatePattern(): void
    {
        Log::info(
            message: "Update pattern",
            context: [
                'pattern' => $this->pattern->getPattern()->toArray(),
            ]
        );
    }

    protected function logCreateCategories(): void
    {
        Log::info(
            message: "Creating categories, if category already exists it will be ignored",
            context: [
                'categories' => array_map(
                    array: $this->pattern->getCategories()->getItems(),
                    callback: fn(CategoryDto $category) => $category->toArray(),
                ),
            ],
        );
    }

    protected function logCreateTags(): void
    {
        Log::info(
            message: "Creating tags, if tag already exists it will be ignored",
            context: [
                'tags' => array_map(
                    array: $this->pattern->getTags()->getItems(),
                    callback: fn(TagDto $tag) => $tag->toArray(),
                ),
            ],
        );
    }

    protected function logDownloadImage(ImageDto &$image): void
    {
        Log::info(
            message: "Start download image",
            context: [
                'image' => $image->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id,
            ],
        );
    }

    protected function logSavedImage(ImageDto &$image, SavedImageDto &$savedImage): void
    {
        Log::info(
            message: "Image saved",
            context: [
                'image' => $image->toArray(),
                'saved_image' => $savedImage->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id,
            ],
        );
    }

    protected function logFailedToDownloadImage(ImageDto &$image, ?Throwable &$th = null): void
    {
        $context = [
            'image' => $image->toArray(),
            'pattern_id' => $this->pattern->getPattern()->id,
        ];

        if ($th instanceof Throwable) {
            $context['error'] = $th->__toString();
        }

        Log::error(
            message: 'Failed to download image',
            context: $context
        );
    }

    protected function logCreateImages(SavedImageDto ...$savedImages): void
    {
        Log::info(
            message: "Creating images, if image with particular hash for specified pattern already exists it will be ignored",
            context: [
                'images' => array_map(
                    array: $savedImages,
                    callback: fn(SavedImageDto $image) => $image->toArray(),
                ),
                'pattern_id' => $this->pattern->getPattern()->id,
            ]
        );
    }

    protected function logDownloadFile(FileDto &$file): void
    {
        Log::info(
            message: "Start download file",
            context: [
                'file' => $file->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id,
            ],
        );
    }

    protected function logGettingDirectYandexDiskUrl(string $url): void
    {
        Log::info(
            message: "Getting direct URL to file on Yandex disk",
            context: [
                'url' => $url,
            ]
        );
    }

    protected function logFailToGetDirectYandexDiskUrl(string $url, ?Throwable &$th = null): void
    {
        $context =  [
            'url' => $url,
        ];

        if ($th instanceof Throwable) {
            $context['error'] = $th->__toString();
        }

        Log::error(
            message: "Failed to get direct URL to file on Yandex disk",
            context: $context,
        );
    }

    protected function logGettingDirectGoogleDriveUrl(string $url): void
    {
        Log::info(
            message: "Getting direct URL to file on Google drive",
            context: [
                'url' => $url,
            ]
        );
    }

    protected function logFailToGetDirectGoogleDriveUrl(string $url): void
    {
        Log::error(
            message: "Failed to get direct URL to file on Google drive",
            context: [
                'url' => $url,
            ]
        );
    }

    protected function logGettingDirectVkUrl(string $url): void
    {
        Log::info(
            message: "Getting direct URL to file on VK",
            context: [
                'url' => $url,
            ]
        );
    }

    protected function logFailToGetDirectVkUrl(string $url): void
    {
        Log::error(
            message: "Failed to get direct URL to file on VK",
            context: [
                'url' => $url,
            ]
        );
    }

    protected function logSavedFile(FileDto &$file, SavedFileDto &$savedFile): void
    {
        Log::info(
            message: "File saved",
            context: [
                'file' => $file->toArray(),
                'saved_file' => $savedFile->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id
            ],
        );
    }

    protected function logCheckingSavedFileMimeType(SavedFileDto &$savedFile): void
    {
        Log::info(
            message: "Checking saved file mime type",
            context: [
                'saved_file' => $savedFile->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id
            ],
        );
    }

    protected function logExtMimeMismatch(SavedFileDto &$savedFile, ?string &$mimeExt): void
    {
        Log::warning(
            message: 'Original file extension is not match with extension based on file mime type',
            context: [
                'saved_file' => $savedFile->toArray(),
                'ext_based_on_mime_type' => $mimeExt,
                'pattern_id' => $this->pattern->getPattern()->id
            ],
        );
    }

    protected function logSavedFileWasMoved(SavedFileDto &$savedFile, SavedFileDto &$newSavedFile): void
    {
        Log::info(
            message: 'Saved file was moved',
            context: [
                'saved_file' => $savedFile->toArray(),
                'new_saved_file' => $newSavedFile->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id
            ],
        );
    }

    protected function logFailedToDownloadFile(FileDto &$file, ?Throwable &$th = null): void
    {
        $context = [
            'file' => $file->toArray(),
            'pattern_id' => $this->pattern->getPattern()->id
        ];

        if ($th instanceof Throwable) {
            $context['error'] = $th->__toString();
        }

        Log::error(
            message: 'Failed to download file',
            context: $context,
        );
    }

    protected function logCreateFiles(SavedFileDto ...$savedFiles): void
    {
        Log::info(
            message: "Creating files, if image with particular hash for specified pattern already exists it will be ignored",
            context: [
                'files' => array_map(
                    array: $savedFiles,
                    callback: fn(SavedFileDto $file) => $file->toArray(),
                ),
                'pattern_id' => $this->pattern->getPattern()->id,
            ]
        );
    }






    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternCategory> $categories
     */
    protected function logAttachCategories(SupportCollection &$categories): void
    {
        Log::info(
            message: "Attaching categories to pattern",
            context: [
                'categories' => $categories->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id,
            ]
        );
    }


    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternTag> $tags
     */
    protected function logAttachTags(SupportCollection &$tags): void
    {
        Log::info(
            message: "Attaching tags to pattern",
            context: [
                'tags' => $tags->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id,
            ]
        );
    }

    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternImage> $images
     */
    protected function logAttachImages(SupportCollection &$images): void
    {
        Log::info(
            message: "Attaching image to pattern",
            context: [
                'images' => $images->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id,
            ]
        );
    }


    /**
     * @param \Illuminate\Support\Collection<\App\Models\PatternFile> $files
     */
    protected function logAttachFiles(SupportCollection &$files): void
    {
        Log::info(
            message: "Attaching files to pattern",
            context: [
                'files' => $files->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id,
            ]
        );
    }







    protected function logDeleteSavedImage(SavedImageDto &$savedImage): void
    {
        Log::info(
            message: 'Deleting saved image',
            context: [
                'saved_image' => $savedImage->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id
            ],
        );
    }

    protected function logDeleteSavedFile(SavedFileDto &$savedFile): void
    {
        Log::info(
            message: 'Deleting saved file',
            context: [
                'saved_file' => $savedFile->toArray(),
                'pattern_id' => $this->pattern->getPattern()->id
            ],
        );
    }







    protected function logDbRollbackedBecauseOfError(Throwable $th): void
    {
        Log::info(
            message: "An error happened while trying {$this->actionName}, all DB changes was rollbacked",
            context: [
                'error' => $th->__toString(),
                'pattern_id' => $this->pattern->getPattern()->id
            ],
        );
    }
}
