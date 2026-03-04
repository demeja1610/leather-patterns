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
use App\Interfaces\Services\FileServiceInterface;
use App\Http\Requests\Admin\Pattern\CreateRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class CreateController extends Controller
{
    public function __construct(
        protected readonly FileServiceInterface $fileService
    ) {}

    public function __invoke(CreateRequest $request): RedirectResponse
    {
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

        $filesUrls = isset($data['files']) ? $data['files'] : [];

        $patternFiles = $filesUrls === []
            ? []
            : $this->makePatternFiles($filesUrls);

        try {
            DB::beginTransaction();

            $pattern = Pattern::query()->create(attributes: $data);

            if ($categoryIds !== []) {
                $pattern->categories()->attach($categoryIds);
            }

            if ($tagIds !== []) {
                $pattern->tags()->attach($tagIds);
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
                        text: __(key: 'pattern.admin.error_while_creating'),
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

        return to_route(route: 'admin.page.patterns.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern.admin.created', replace: ['title' => $pattern->title]),
                    type: NotificationTypeEnum::SUCCESS,
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

        foreach ($urls as $url) {
            $storagePath = parse_url($url, PHP_URL_PATH);
            $path = str_replace('/storage/', '', $storagePath);

            if (Storage::disk('public')->exists($path)) {
                $publicPath = Storage::disk('public')->path($path);

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

        foreach ($urls as $url) {
            $storagePath = parse_url($url, PHP_URL_PATH);
            $path = str_replace('/storage/', '', $storagePath);

            if (Storage::disk('public')->exists($path)) {
                $publicPath = Storage::disk('public')->path($path);

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
