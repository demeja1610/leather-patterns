<?php

declare(strict_types=1);

namespace App\Console\Commands\Import;

use GuzzleHttp\Client;
use App\Models\PatternImage;
use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\GuzzleException;

class ImportPatternImagesCommand extends Command
{
    protected $signature = 'import:pattern-images';

    protected $description = 'Import pattern images';

    protected ?\GuzzleHttp\Client $client = null;

    protected ?string $userAgent = null;

    public function handle(): void
    {
        $this->info(message: 'Importing pattern images...');

        DB::connection('mysql_import')->table(table: 'image_pattern')
            ->join(table: 'images', first: 'image_pattern.image_id', operator: '=', second: 'images.id')
            ->join(table: 'patterns', first: 'image_pattern.pattern_id', operator: '=', second: 'patterns.id')
            ->where(column: 'patterns.source', operator: '!=', value: PatternSourceEnum::SKINCUTS->value)
            ->orderBy(column: 'image_pattern.image_id')
            ->select(columns: [
                'image_pattern.image_id as image_id',
                'image_pattern.pattern_id as pattern_id',
                'images.file_path as image_file_path',
                'images.sha_256_hash as image_sha_256_hash',
                'images.created_at as image_created_at',
                'images.updated_at as image_updated_at',
                'images.source_url as image_source_url',
            ])
            //     ->where('images.file_path', '=', null)
            //     ->pluck('image_id');

            // $this->info("Total pattern images to import: {$count}");
            ->chunk(
                count: 500,
                callback: function (Collection $chunk): void {
                    $from = $chunk->first()->image_id;
                    $to = $chunk->last()->image_id;
                    $count = $chunk->count();

                    $this->info(message: "Importing pattern images from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    $this->info(message: "Getting images meta information for {$count} images...");

                    foreach ($chunk as $item) {
                        if ($item->image_file_path === null) {
                            $this->info(message: "Image file path is null for image ID: {$item->image_id}, trying to download...");

                            $image = $this->downloadImage(url: $item->image_source_url);

                            if ($image instanceof PatternImage) {
                                $this->info(message: "Successfully downloaded image for ID: {$item->image_id}");

                                $item->image_file_path = $image->path;
                                $item->image_sha_256_hash = $image->hash;
                            } else {
                                $this->error(message: "Failed to download image for ID: {$item->image_id}, skipping...");

                                continue;
                            }
                        }

                        $exists = Storage::disk('public')->exists($item->image_file_path);

                        if (!$exists) {
                            $this->info(message: "Image file does not exist: {$item->image_file_path}");

                            continue;
                        }

                        $fullPath = Storage::disk('public')->path($item->image_file_path);
                        $ext = strtolower(string: File::extension($fullPath));
                        $mime = File::mimeType($fullPath);
                        $size = File::size($fullPath);

                        $toInsert[] = [
                            'pattern_id' => $item->pattern_id,
                            'path' => $item->image_file_path,
                            'extension' => $ext,
                            'size' => $size,
                            'mime_type' => $mime,
                            'hash_algorithm' => 'sha256',
                            'hash' => $item->image_sha_256_hash,
                            'created_at' => $item->image_created_at,
                            'updated_at' => $item->image_updated_at,
                        ];
                    }

                    DB::table('pattern_images')->insert(values: $toInsert);
                },
            );

        $this->info(message: "All pattern images imported successfully.");
    }

    protected function downloadImage(string $url): ?PatternImage
    {
        $this->info(message: "Downloading image from URL: {$url}");

        if (!$this->client instanceof Client) {
            $this->client = new \GuzzleHttp\Client();
        }

        if (!$this->userAgent) {
            $this->userAgent = implode(separator: ' ', array: [
                'Mozilla/5.0',
                '(Windows NT 10.0; Win64; x64)',
                'AppleWebKit/537.36',
                '(KHTML, like Gecko)',
                'Chrome/91.0.4472.124',
                'Safari/537.36',
            ]);
        }

        $image = new PatternImage();

        try {
            $response = $this->client->get(uri: $url, options: [
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
                'allow_redirects' => true,
            ]);

            $extension = pathinfo(path: parse_url(url: $image->url, component: PHP_URL_PATH), flags: PATHINFO_EXTENSION) ?: 'jpg';
            $encodedFileName = pathinfo(path: basename(path: $image->url), flags: PATHINFO_FILENAME) . '-' . time() . '-' . uniqid();
            $fileName = "images/{$encodedFileName}.{$extension}";

            Storage::disk('public')->put(
                $fileName,
                $response->getBody()->getContents(),
            );
        } catch (GuzzleException $guzzleException) {
            $this->error(
                message: "Failed to download image {$image->id}: " . $guzzleException->getMessage(),
            );

            return null;
        }

        $publicPathToFile = public_path(path: "storage/{$fileName}");

        $image->path = $fileName;
        $image->hash = hash_file(algo: 'sha256', filename: $publicPathToFile);

        return $image;
    }
}
