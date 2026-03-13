<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Action;

use App\Models\Pattern;
use App\Models\PatternFile;
use App\Models\PatternImage;
use App\Enum\NotificationTypeEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\Pattern\EditRequest;
use App\Interfaces\Services\FileServiceInterface;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class EditController extends Controller
{
    public function __construct(
        protected readonly FileServiceInterface $fileService
    ) {}

    public function __invoke($id, EditRequest $request): RedirectResponse
    {
        $pattern = Pattern::query()->where('id', $id)->first();

        if (!$pattern instanceof Pattern) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $data = array_merge(
            $request->validated(),
            [
                'is_published' => (bool) $request->input(key: 'is_published', default: false),
            ],
        );

        $categoryIds = [];

        if (isset($data['category_id'])) {
            $categoryIds = $data['category_id'];

            unset($data['category_id']);
        }

        $tagIds = [];

        if (isset($data['tag_id'])) {
            $tagIds = $data['tag_id'];

            unset($data['tag_id']);
        }

        $imagesUrls = isset($data['images']) ? $data['images'] : [];

        $patternImages = $imagesUrls === []
            ? []
            : $this->makePatternImages($imagesUrls);

        $removePatternImagesUrls = isset($data['remove_images']) ? $data['remove_images'] : [];

        $filesUrls = isset($data['files']) ? $data['files'] : [];

        $patternFiles = $filesUrls === []
            ? []
            : $this->makePatternFiles($filesUrls);

        $removePatternFilesUrls = isset($data['remove_files']) ? $data['remove_files'] : [];

        try {
            DB::beginTransaction();

            $updated = $pattern->update($data);

            $pattern->categories()->sync($categoryIds);

            $pattern->tags()->sync($tagIds);

            if ($removePatternImagesUrls !== []) {
                $pattern->load('images');

                $newPatternImage = new PatternImage();

                /**
                 * @var \Illuminate\Filesystem\Filesystem
                 */
                $disk  = Storage::disk($newPatternImage->getSaveDiskName());

                $toDeleteImages = $pattern->images->filter(fn(PatternImage $patternImage) => in_array(
                    haystack: $removePatternImagesUrls,
                    needle: asset($disk->url($patternImage->path)),
                ));

                $toDeleteImages->each(fn(PatternImage $image) => $image->delete());
            }

            if ($removePatternFilesUrls !== []) {
                $pattern->load('files');

                 $newPatternFile = new PatternFile();

                /**
                 * @var \Illuminate\Filesystem\Filesystem
                 */
                $disk  = Storage::disk($newPatternFile->getSaveDiskName());

                $toDeleteFiles = $pattern->files->filter(fn(PatternFile $patternFile) => in_array(
                    haystack: $removePatternFilesUrls,
                    needle: asset($disk->url($patternFile->path)),
                ));

                $toDeleteFiles->each(fn(PatternFile $file) => $file->delete());
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            $request->flashSelectedAuthor();
            $request->flashSelectedCategories();
            $request->flashSelectedTags();

            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern.admin.error_while_updating'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        if ($patternImages !== []) {
            $pattern->images()->saveMany($patternImages);
        }

        if ($patternFiles !== []) {
            $pattern->files()->saveMany($patternFiles);
        }

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated > 0
                    ? new SessionNotificationDto(
                        text: __(key: 'pattern.admin.updated', replace: ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern.admin.failed_to_update', replace: ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    /**
     * @return array<PatternImage>
     */
    protected function makePatternImages(array $urls): array
    {
        $images = [];

        $newPatternImage = new PatternImage();

        foreach ($urls as $url) {
            $storagePath = parse_url($url, PHP_URL_PATH);
            $path = str_replace('/storage/', '', $storagePath);

            if (Storage::disk($newPatternImage->getSaveDiskName())->exists($path)) {
                $publicPath = Storage::disk($newPatternImage->getSaveDiskName())->path($path);

                $ext = $this->fileService->getExtension($publicPath);
                $size = $this->fileService->getSize($publicPath);
                $mime = $this->fileService->getMimeType($publicPath);
                $hash = $this->fileService->getHash($publicPath);
                $algo = $this->fileService->getHashAlgo();

                $images[] = new PatternImage([
                    'path' => $path,
                    'extension' => $ext,
                    'size' => $size,
                    'mime_type' => $mime,
                    'hash_algorithm' => $algo,
                    'hash' => $hash,
                ]);
            }
        }

        return $images;
    }

    /**
     * @return array<PatternFile>
     */
    protected function makePatternFiles(array $urls): array
    {
        $files = [];

        $newPatternFile = new PatternFile();

        foreach ($urls as $url) {
            $storagePath = parse_url($url, PHP_URL_PATH);
            $path = str_replace('/storage/', '', $storagePath);

            if (Storage::disk($newPatternFile->getSaveDiskName())->exists($path)) {
                $publicPath = Storage::disk($newPatternFile->getSaveDiskName())->path($path);

                $ext = $this->fileService->getExtension($publicPath);
                $size = $this->fileService->getSize($publicPath);
                $mime = $this->fileService->getMimeType($publicPath);
                $type = $this->fileService->getFileType($mime);
                $hash = $this->fileService->getHash($publicPath);
                $algo = $this->fileService->getHashAlgo();

                $files[] = new PatternFile([
                    'path' => $path,
                    'type' => $type?->value,
                    'extension' => $ext,
                    'size' => $size,
                    'mime_type' => $mime,
                    'hash_algorithm' => $algo,
                    'hash' => $hash,
                ]);
            }
        }

        return $files;
    }
}
