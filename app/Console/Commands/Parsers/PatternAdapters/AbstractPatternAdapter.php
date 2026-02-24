<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use App\Models\Pattern;
use App\Enum\FileTypeEnum;
use App\Models\PatternMeta;
use App\Models\PatternVideo;
use App\Enum\VideoSourceEnum;
use App\Models\PatternReview;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\GuzzleException;
use App\Console\Commands\Parsers\AbstractAdapter;
use App\Interfaces\Services\ParserServiceInterface;

abstract class AbstractPatternAdapter extends AbstractAdapter
{
    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {}

    protected function calculateFileHash(string $filePath): string
    {
        return hash_file(algo: 'sha256', filename: $filePath);
    }

    protected function getFileExtension(string $filePath): ?string
    {
        return pathinfo(path: $filePath, flags: PATHINFO_EXTENSION);
    }

    protected function generateFileName(?string $prefix = null): string
    {
        return uniqid(prefix: $prefix ?: 'pattern_', more_entropy: true);
    }

    protected function deleteFileIfExists(string $filePath): void
    {
        if (Storage::disk('public')->exists($filePath)) {
            $this->warn(message: "Deleting file: {$filePath}");

            Storage::disk('public')->delete($filePath);
        }
    }

    protected function deleteImagesIfExists(array $imagePaths): void
    {
        foreach ($imagePaths as $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                $this->warn(message: "Deleting image: {$imagePath}");

                Storage::disk('public')->delete($imagePath);
            }
        }
    }

    protected function getDirectYandexDiskFileLink(string $url): ?string
    {
        try {
            $response = $this->parserService->getClient()->get(
                uri: "https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key={$url}",
            );
        } catch (Throwable $throwable) {
            $this->error(
                message: "Failed to get direct Yandex disk file link for pattern {$url}: " . $throwable->getMessage(),
            );

            return null;
        }

        $body = $response->getBody()->getContents();

        $json = json_decode(json: $body, associative: true);

        if (!isset($json['href'])) {
            $this->error(message: "Failed to get direct Yandex disk file link for pattern {$url}");

            return null;
        }

        return $json['href'];
    }

    protected function getDirectGoogleDriveFileLink(string $url): ?string
    {
        $fileId = null;

        preg_match(pattern: '/\/d\/([a-zA-Z0-9_-]+)/', subject: $url, matches: $matches);

        if (isset($matches[1])) {
            $fileId = $matches[1];
        }

        return $fileId
            ? "https://drive.google.com/uc?export=download&id={$fileId}"
            : null;
    }

    protected function getFileMimeType(string $filePath): ?string
    {
        $finfo = finfo_open(flags: FILEINFO_MIME_TYPE);

        $mimeType = finfo_file(finfo: $finfo, filename: $filePath);

        finfo_close(finfo: $finfo);

        return $mimeType;
    }

    protected function parseVideosFromString(string $content, Pattern $pattern): ?array
    {
        $youtubeVideoIds = $this->parserService->getYoutubeVideoIdsFromString($content);
        $vkVideoIds = $this->parserService->getVkVideoIdsFromString($content);

        $ytCount = count(value: $youtubeVideoIds);
        $vkCount = count(value: $vkVideoIds);

        if ($ytCount !== 0) {
            $this->info(message: "Found {$ytCount} YouTube video(s) for pattern {$pattern->id}");
        }

        if ($vkCount !== 0) {
            $this->info(message: "Found {$vkCount} VK video(s) for pattern {$pattern->id}");
        }

        $videos = [];

        foreach ($youtubeVideoIds as $videoId) {
            $videos[] = [
                'source' => VideoSourceEnum::YOUTUBE,
                'video_id' => $videoId,
            ];
        }

        foreach ($vkVideoIds as $videoId) {
            $videos[] = [
                'source' => VideoSourceEnum::VK,
                'video_id' => $videoId,
            ];
        }

        return $videos;
    }

    protected function getImageUrlFromSrcset(string $srcset): string
    {
        $srcsetPairs = array_filter(array: explode(separator: ',', string: $srcset));
        $maxWidth = 0;
        $imageUrl = '';

        foreach ($srcsetPairs as $pair) {
            $parts = explode(separator: ' ', string: trim(string: $pair));

            if (count(value: $parts) === 2) {
                $url = $parts[0];
                $width = (int) str_replace(search: 'w', replace: '', subject: $parts[1]);

                if ($width > $maxWidth) {
                    $maxWidth = $width;
                    $imageUrl = $url;
                }
            }
        }

        return $imageUrl;
    }

    protected function prepareVideoForCreation(VideoSourceEnum $source, string $videoId): PatternVideo
    {
        return new PatternVideo(attributes: [
            'source' => $source,
            'source_identifier' => $videoId,
            'url' => match ($source) {
                VideoSourceEnum::YOUTUBE => "https://www.youtube.com/watch?v={$videoId}",
                VideoSourceEnum::VK => "https://vkvideo.ru/video{$videoId}",
                default => null,
            },
        ]);
    }

    protected function prepareReviewForCreation(
        ?string $comment = null,
        ?float $rating = null,
        ?string $reviewerName = null,
    ): PatternReview {
        return new PatternReview(attributes: [
            'rating' => $rating ?? 0,
            'reviewer_name' => $reviewerName ?? 'Unknown',
            'comment' => $comment ?? '',
        ]);
    }

    /**
     * @param array<PatternReview> $reviews
     */
    protected function filterExistingReviews(Pattern $pattern, array $reviews): array
    {
        $reviewsToCreate = [];

        if (!$pattern->relationLoaded(key: 'reviews')) {
            $pattern->load(relations: 'reviews');
        }

        $existingPatternReviews = $pattern->reviews->toArray();

        foreach ($reviews as $review) {
            $isAlreadyExists = array_filter(
                array: $existingPatternReviews,
                callback: fn(array $patternReview): bool => $patternReview['comment'] === $review->comment,
            );

            if ($isAlreadyExists !== []) {
                continue;
            }

            $reviewsToCreate[] = $review;
        }

        return $reviewsToCreate;
    }

    protected function bindFiles(Pattern $pattern, array $filePaths): void
    {
        $filePathsStr = implode(separator: ', ', array: $filePaths);

        $this->info(message: "Binding files ({$filePathsStr}) for pattern: {$pattern->id}");

        foreach ($filePaths as $filePath) {
            $publicPath = public_path(path: "storage/{$filePath}");
            $ext = pathinfo(path: (string) $filePath, flags: PATHINFO_EXTENSION);
            $size = filesize(filename: $publicPath);
            $mime = $this->getFileMimeType(filePath: $publicPath);
            $type = FileTypeEnum::fromMimeType(mimeType: $mime);
            $hash = $this->calculateFileHash(filePath: $publicPath);

            $pattern->files()->create(attributes: [
                'path' => $filePath,
                'extension' => $ext,
                'size' => $size,
                'mime_type' => $mime,
                'type' => $type,
                'hash_algorithm' => 'sha256',
                'hash' => $hash,
            ]);
        }
    }

    protected function bindImages(Pattern $pattern, array $imagePaths): void
    {
        $imagePathsStr = implode(separator: ', ', array: $imagePaths);

        $this->info(message: "Binding images ({$imagePathsStr}) for pattern: {$pattern->id}");

        $images = [];

        foreach ($imagePaths as $imagePath) {
            $publicPath = public_path(path: "storage/{$imagePath}");
            $ext = pathinfo(path: (string) $imagePath, flags: PATHINFO_EXTENSION);
            $size = filesize(filename: $publicPath);
            $mime = $this->getFileMimeType(filePath: $publicPath);
            $hash = $this->calculateFileHash(filePath: $publicPath);

            $images[] = [
                'path' => $imagePath,
                'extension' => $ext,
                'size' => $size,
                'mime_type' => $mime,
                'hash_algorithm' => 'sha256',
                'hash' => $hash,
            ];
        }

        $pattern->images()->createMany(records: $images);
    }

    protected function changePatternMeta(Pattern $pattern): void
    {
        $this->info(message: "Changing pattern meta for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update(values: [
            'pattern_downloaded' => true,
            'images_downloaded' => true,
        ]);
    }

    protected function setPatternVideoChecked(Pattern $pattern): void
    {
        $this->info(message: "Setting video checked for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update(values: [
            'is_video_checked' => true,
        ]);
    }

    protected function setPatternReviewChecked(Pattern $pattern): void
    {
        $this->info(message: "Setting review checked for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update(values: [
            'reviews_updated_at' => now(),
        ]);
    }

    protected function setDownloadUrlWrong(Pattern $pattern): void
    {
        $this->warn(message: "Setting download URL wrong for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update(values: [
            'is_download_url_wrong' => true,
        ]);
    }

    protected function downloadPatternFile(Pattern $pattern, string $url, array $extraHeaders = [], $postParams = []): ?string
    {
        $this->info(message: "Downloading pattern file from: {$url}");

        $downloadUrl = $url;

        if (str_contains(haystack: $url, needle: 'yadi.sk') || str_contains(haystack: $url, needle: 'disk.yandex')) {
            $this->info(message: "Getting direct Yandex disk file link");
            $downloadUrl = $this->getDirectYandexDiskFileLink(url: $url);
            if (!$downloadUrl) {
                $this->error(message: "Failed to get direct Yandex disk file link for pattern {$url}");

                return null;
            }
        } elseif (str_contains(haystack: $url, needle: 'vk.com')) {
            $this->warn(message: "Currently unable to download pattern from vk.com. Skipping...");
            return null;
        } elseif (str_contains(haystack: $url, needle: 'drive.google.com')) {
            $this->info(message: "Getting direct Google Drive file link");
            $downloadUrl = $this->getDirectGoogleDriveFileLink(url: $url);
            if (!$downloadUrl) {
                $this->error(message: "Failed to get direct Google Drive file link for pattern {$url}");

                return null;
            }
        }

        try {
            $params = [
                'allow_redirects' => true,
            ];

            if ($extraHeaders !== []) {
                $params['headers'] = $extraHeaders;
            }

            if (!empty($postParams)) {
                $params['form_params'] = $postParams;
            }

            if (!empty($postParams)) {
                $response = $this->parserService->getClient()->post(uri: $downloadUrl, options: $params);
            } else {
                $response = $this->parserService->getClient()->get(uri: $downloadUrl, options: $params);
            }

            $extension = $this->getFileExtension(
                filePath: parse_url(url: $downloadUrl, component: PHP_URL_PATH),
            ) ?: 'pdf';

            $encodedFileName = $this->generateFileName(
                prefix: 'pattern_',
            );

            $savePath = "patterns/{$pattern->id}/{$encodedFileName}.{$extension}";

            Storage::disk('public')->put(
                $savePath,
                $response->getBody()->getContents(),
            );

            $this->info(message: "Checking file extension for pattern {$pattern->id}, original extension: {$extension}");

            $mimeType = $this->getFileMimeType(filePath: public_path(path: "storage/{$savePath}"));

            if (str_contains(haystack: (string) $mimeType, needle: 'video')) {
                $this->warn(message: "Video file detected for pattern {$pattern->id}, skipping and deleting this file...");

                $this->deleteFileIfExists(filePath: $savePath);

                return null;
            }

            $exts = [
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

            $ext = $exts[$mimeType] ?? $extension;

            $filePath = $savePath;

            if ($ext !== $extension) {
                $this->warn(message: "Original file extension for pattern {$pattern->id}: {$extension} is wrong, renaming to: {$ext}");

                $newFileName = str_replace(search: ".{$extension}", replace: ".{$ext}", subject: $savePath);

                $filePath = public_path(path: "storage/{$newFileName}");

                rename(
                    from: public_path(path: "storage/{$savePath}"),
                    to: $filePath,
                );

                $this->warn(message: "Renamed file for pattern {$pattern->id}: {$savePath} to {$filePath}");

                $filePath = $newFileName;
            } else {
                $this->info(message: "File extension for pattern {$pattern->id} is correct: {$extension}");
            }

            return $filePath;
        } catch (GuzzleException $guzzleException) {
            $this->error(
                message: "Failed to download pattern {$pattern->id}: " . $guzzleException->getMessage(),
            );

            return null;
        }

        $this->success(message: "Downloaded pattern {$pattern->id}");
    }

    protected function downloadPatternImages(Pattern $pattern, array $imageUrls): array
    {
        $imagePaths = [];

        foreach ($imageUrls as $imageUrl) {
            $this->info(
                message: "Downloading image for pattern {$pattern->id} from: {$imageUrl}",
            );

            try {
                $response = $this->parserService->getClient()->get(uri: $imageUrl, options: [
                    'allow_redirects' => true,
                ]);

                $extension = pathinfo(path: parse_url(url: (string) $imageUrl, component: PHP_URL_PATH), flags: PATHINFO_EXTENSION) ?: 'jpg';
                $encodedFileName = $this->generateFileName(prefix: 'image_');
                $fileName = "images/patterns/{$pattern->id}/{$encodedFileName}.{$extension}";

                Storage::disk('public')->put(
                    $fileName,
                    $response->getBody()->getContents(),
                );

                $imagePaths[] = $fileName;
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                $this->error(message: "Failed to download image for pattern {$pattern->id} from: {$imageUrl}: " . $e->getMessage());
            }
        }

        $this->success(message: "Downloaded images for pattern {$pattern->id}");

        return $imagePaths;
    }
}
