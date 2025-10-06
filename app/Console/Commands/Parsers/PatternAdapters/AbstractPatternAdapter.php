<?php

namespace App\Console\Commands\Parsers\PatternAdapters;

use Throwable;
use App\Models\Pattern;
use App\Enum\FileTypeEnum;
use App\Models\PatternMeta;
use App\Models\PatternVideo;
use App\Enum\VideoSourceEnum;
use App\Models\PatternReview;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\Parsers\AbstractAdapter;
use App\Interfaces\Services\ParserServiceInterface;

abstract class AbstractPatternAdapter extends AbstractAdapter
{
    public function __construct(
        protected ParserServiceInterface $parserService
    ) {}

    protected function calculateFileHash(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    protected function getFileExtension(string $filePath): ?string
    {
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }

    protected function generateFileName(?string $prefix = null): string
    {
        return uniqid($prefix ?: 'pattern_', true);
    }

    protected function deleteFileIfExists(string $filePath): void
    {
        if (Storage::disk('public')->exists($filePath)) {
            $this->warn("Deleting file: {$filePath}");

            Storage::disk('public')->delete($filePath);
        }
    }

    protected function deleteImagesIfExists(array $imagePaths): void
    {
        foreach ($imagePaths as $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                $this->warn("Deleting image: {$imagePath}");

                Storage::disk('public')->delete($imagePath);
            }
        }
    }

    protected function getDirectYandexDiskFileLink(string $url): ?string
    {
        try {
            $response = $this->parserService->getClient()->get(
                "https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key={$url}",
            );
        } catch (Throwable $th) {
            $this->error("Failed to get direct Yandex disk file link for pattern {$url}: " . $th->getMessage());

            return null;
        }

        $body = $response->getBody()->getContents();

        $json = json_decode($body, true);

        if (!isset($json['href'])) {
            $this->error("Failed to get direct Yandex disk file link for pattern {$url}");

            return null;
        }

        return $json['href'];
    }

    protected function getDirectGoogleDriveFileLink(string $url): ?string
    {
        $fileId = null;

        preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);

        if (isset($matches[1])) {
            $fileId = $matches[1];
        }

        return $fileId
            ? "https://drive.google.com/uc?export=download&id={$fileId}"
            : null;
    }

    protected function getFileMimeType(string $filePath): ?string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeType = finfo_file($finfo, $filePath);

        finfo_close($finfo);

        return $mimeType;
    }

    protected function parseVideosFromString(string $content, Pattern $pattern): ?array
    {
        $youtubeVideoIds = $this->parserService->getYoutubeVideoIdsFromString($content);
        $vkVideoIds = $this->parserService->getVkVideoIdsFromString($content);

        $ytCount = count($youtubeVideoIds);
        $vkCount = count($vkVideoIds);

        if ($ytCount) {
            $this->info("Found {$ytCount} YouTube video(s) for pattern {$pattern->id}");
        }

        if ($vkCount) {
            $this->info("Found {$vkCount} VK video(s) for pattern {$pattern->id}");
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
        $srcsetPairs = array_filter(explode(',', $srcset));
        $maxWidth = 0;
        $imageUrl = '';

        foreach ($srcsetPairs as $pair) {
            $parts = explode(' ', trim($pair));

            if (count($parts) === 2) {
                $url = $parts[0];
                $width = (int) str_replace('w', '', $parts[1]);

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
        return new PatternVideo([
            'source' => $source,
            'source_identifier' => $videoId,
            'url' => match ($source) {
                VideoSourceEnum::YOUTUBE => "https://www.youtube.com/watch?v={$videoId}",
                VideoSourceEnum::VK => "https://vkvideo.ru/video{$videoId}",
                default => null,
            }
        ]);
    }

    protected function prepareReviewForCreation(
        ?string $comment = null,
        ?float $rating = null,
        ?string $reviewerName = null,
        ?string $reviewedAt = null
    ): PatternReview {
        return new PatternReview([
            'rating' => $rating ?? 0,
            'reviewer_name' => $reviewerName ?? 'Unknown',
            'reviewed_at' => $reviewedAt ?? now(),
            'comment' => $comment ?? '',
        ]);
    }

    /**
     * @param Pattern $pattern
     * @param array<PatternReview> $reviews
     */
    protected function filterExistingReviews(Pattern $pattern, array $reviews): array
    {
        $reviewsToCreate = [];

        if (!$pattern->relationLoaded('reviews')) {
            $pattern->load('reviews');
        }

        $existingPatternReviews = $pattern->reviews->toArray();

        foreach ($reviews as $review) {
            $isAlreadyExists = array_filter(
                array: $existingPatternReviews,
                callback: function ($patternReview) use ($review) {
                    return $patternReview['comment'] === $review->comment;
                },
            );

            if (count($isAlreadyExists) > 0) {
                continue;
            }

            $reviewsToCreate[] = $review;
        }

        return $reviewsToCreate;
    }

    protected function bindFiles(Pattern $pattern, array $filePaths): void
    {
        $filePathsStr = implode(', ', $filePaths);

        $this->info("Binding files ({$filePathsStr}) for pattern: {$pattern->id}");

        foreach ($filePaths as $filePath) {
            $publicPath = public_path("storage/{$filePath}");
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $size = filesize($publicPath);
            $mime = $this->getFileMimeType($publicPath);
            $type = FileTypeEnum::fromMimeType($mime);
            $hash = $this->calculateFileHash($publicPath);

            $pattern->files()->create([
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
        $imagePathsStr = implode(', ', $imagePaths);

        $this->info("Binding images ({$imagePathsStr}) for pattern: {$pattern->id}");

        $images = [];

        foreach ($imagePaths as $imagePath) {
            $publicPath = public_path("storage/{$imagePath}");
            $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
            $size = filesize($publicPath);
            $mime = $this->getFileMimeType($publicPath);
            $hash = $this->calculateFileHash($publicPath);

            $images[] = [
                'path' => $imagePath,
                'extension' => $ext,
                'size' => $size,
                'mime_type' => $mime,
                'hash_algorithm' => 'sha256',
                'hash' => $hash,
            ];
        }

        $pattern->images()->createMany($images);
    }

    protected function changePatternMeta(Pattern $pattern): void
    {
        $this->info("Changing pattern meta for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update([
            'pattern_downloaded' => true,
            'images_downloaded' => true,
        ]);
    }

    protected function setPatternVideoChecked(Pattern $pattern): void
    {
        $this->info("Setting video checked for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update([
            'is_video_checked' => true,
        ]);
    }

    protected function setPatternReviewChecked(Pattern $pattern): void
    {
        $this->info("Setting review checked for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update([
            'reviews_updated_at' => now(),
        ]);
    }

    protected function setDownloadUrlWrong(Pattern $pattern): void
    {
        $this->warn("Setting download URL wrong for pattern: {$pattern->id}");

        PatternMeta::query()->where('pattern_id', $pattern->id)->update([
            'is_download_url_wrong' => true,
        ]);
    }

    protected function downloadPatternFile(Pattern $pattern, string $url, array $extraHeaders = [], $postParams = []): ?string
    {
        $this->info("Downloading pattern file from: $url");

        $downloadUrl = $url;

        if (str_contains($url, 'yadi.sk') || str_contains($url, 'disk.yandex')) {
            $this->info("Getting direct Yandex disk file link");

            $downloadUrl = $this->getDirectYandexDiskFileLink($url);

            if (!$downloadUrl) {
                $this->error("Failed to get direct Yandex disk file link for pattern {$url}");

                return null;
            }
        } else if (str_contains($url, 'vk.com')) {
            $this->warn("Currently unable to download pattern from vk.com. Skipping...");

            return null;
        } else if (str_contains($url, 'drive.google.com')) {
            $this->info("Getting direct Google Drive file link");

            $downloadUrl = $this->getDirectGoogleDriveFileLink($url);

            if (!$downloadUrl) {
                $this->error("Failed to get direct Google Drive file link for pattern {$url}");

                return null;
            }
        }

        try {
            $params = [
                'allow_redirects' => true,
            ];

            if (!empty($extraHeaders)) {
                $params['headers'] = $extraHeaders;
            }

            if (!empty($postParams)) {
                $params['form_params'] = $postParams;
            }

            if (!empty($postParams)) {
                $response = $this->parserService->getClient()->post($downloadUrl, $params);
            } else {
                $response = $this->parserService->getClient()->get($downloadUrl, $params);
            }

            $extension = $this->getFileExtension(
                filePath: parse_url($downloadUrl, PHP_URL_PATH)
            ) ?: 'pdf';

            $encodedFileName = $this->generateFileName(
                prefix: 'pattern_'
            );

            $savePath = "patterns/{$pattern->id}/{$encodedFileName}.{$extension}";

            Storage::disk('public')->put(
                $savePath,
                $response->getBody()->getContents(),
            );

            $this->info("Checking file extension for pattern {$pattern->id}, original extension: {$extension}");

            $mimeType = $this->getFileMimeType(public_path("storage/{$savePath}"));

            if (str_contains($mimeType, 'video')) {
                $this->warn("Video file detected for pattern {$pattern->id}, skipping and deleting this file...");

                $this->deleteFileIfExists($savePath);

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
                $this->warn("Original file extension for pattern {$pattern->id}: {$extension} is wrong, renaming to: {$ext}");

                $newFileName = str_replace(".{$extension}", ".{$ext}", $savePath);

                $filePath = public_path("storage/{$newFileName}");

                rename(
                    from: public_path("storage/{$savePath}"),
                    to: $filePath
                );

                $this->warn("Renamed file for pattern {$pattern->id}: {$savePath} to {$filePath}");

                $filePath = $newFileName;
            } else {
                $this->info("File extension for pattern {$pattern->id} is correct: {$extension}");
            }

            return $filePath;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $this->error("Failed to download pattern {$pattern->id}: " . $e->getMessage());

            return null;
        }

        $this->success("Downloaded pattern {$pattern->id}");
    }

    protected function downloadPatternImages(Pattern $pattern, array $imageUrls): array
    {
        $imagePaths = [];

        foreach ($imageUrls as $imageUrl) {
            $this->info("Downloading image for pattern {$pattern->id} from: $imageUrl");

            try {
                $response = $this->parserService->getClient()->get($imageUrl, [
                    'allow_redirects' => true,
                ]);

                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $encodedFileName = $this->generateFileName(prefix: 'image_');
                $fileName = "images/patterns/{$pattern->id}/{$encodedFileName}.{$extension}";

                Storage::disk('public')->put(
                    $fileName,
                    $response->getBody()->getContents(),
                );

                $imagePaths[] = $fileName;
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                $this->error("Failed to download image for pattern {$pattern->id} from: {$imageUrl}: " . $e->getMessage());
            }
        }

        $this->success("Downloaded images for pattern {$pattern->id}");

        return $imagePaths;
    }
}
