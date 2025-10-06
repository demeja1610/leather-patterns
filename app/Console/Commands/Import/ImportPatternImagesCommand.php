<?php

namespace App\Console\Commands\Import;

use App\Models\PatternImage;
use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportPatternImagesCommand extends Command
{
    protected $signature = 'import:pattern-images';
    protected $description = 'Import pattern images';
    protected ?\GuzzleHttp\Client $client = null;
    protected ?string $userAgent = null;

    public function handle()
    {
        $this->info('Importing pattern images...');

        $count = DB::connection('mysql_import')->table('image_pattern')
            ->join('images', 'image_pattern.image_id', '=', 'images.id')
            ->join('patterns', 'image_pattern.pattern_id', '=', 'patterns.id')
            ->where('patterns.source', '!=', PatternSourceEnum::SKINCUTS->value)
            ->orderBy('image_pattern.image_id')
            ->select([
                'image_pattern.image_id as image_id',
                'image_pattern.pattern_id as pattern_id',
                'images.file_path as image_file_path',
                'images.sha_256_hash as image_sha_256_hash',
                'images.created_at as image_created_at',
                'images.updated_at as image_updated_at',
                'images.source_url as image_source_url'
            ])
            //     ->where('images.file_path', '=', null)
            //     ->pluck('image_id');

            // $this->info("Total pattern images to import: {$count}");
            ->chunk(
                count: 500,
                callback: function (Collection $chunk) {
                    $from = $chunk->first()->image_id;
                    $to = $chunk->last()->image_id;
                    $count = $chunk->count();

                    $this->info("Importing pattern images from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    $this->info("Getting images meta information for {$count} images...");

                    foreach ($chunk as $item) {
                        if ($item->image_file_path === null) {
                            $this->info("Image file path is null for image ID: {$item->image_id}, trying to download...");

                            $image = $this->downloadImage($item->image_source_url);

                            if ($image) {
                                $this->info("Successfully downloaded image for ID: {$item->image_id}");

                                $item->image_file_path = $image->path;
                                $item->image_sha_256_hash = $image->hash;
                            } else {
                                $this->error("Failed to download image for ID: {$item->image_id}, skipping...");

                                continue;
                            }
                        }

                        $exists = Storage::disk('public')->exists($item->image_file_path);

                        if (!$exists) {
                            $this->info("Image file does not exist: {$item->image_file_path}");

                            continue;
                        }

                        $fullPath = Storage::disk('public')->path($item->image_file_path);
                        $ext = strtolower(File::extension($fullPath));
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

                    DB::table('pattern_images')->insert($toInsert);
                }
            );

        $this->info("All pattern images imported successfully.");
    }

    protected function downloadImage(string $url): ?PatternImage
    {
        $this->info("Downloading image from URL: {$url}");

        if (!$this->client) {
            $this->client = new \GuzzleHttp\Client();
        }

        if (!$this->userAgent) {
            $this->userAgent = implode(' ', [
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
            $response = $this->client->get($url, [
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
                'allow_redirects' => true,
            ]);

            $extension = pathinfo(parse_url($image->url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $encodedFileName = pathinfo(basename($image->url), PATHINFO_FILENAME) . '-' . time() . '-' . uniqid();
            $fileName = "images/{$encodedFileName}.{$extension}";

            Storage::disk('public')->put(
                $fileName,
                $response->getBody()->getContents(),
            );
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $this->error("Failed to download image {$image->id}: " . $e->getMessage());

            return null;
        }

        $publicPathToFile = public_path("storage/{$fileName}");

        $image->path = $fileName;
        $image->hash = hash_file('sha256', $publicPathToFile);

        return $image;
    }
}
