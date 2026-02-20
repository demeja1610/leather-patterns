<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers;

use Exception;
use App\Models\Pattern;
use App\Models\PatternVideo;
use App\Enum\VideoSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Facades\DB;
use App\Interfaces\Services\ParserServiceInterface;

class ParsePatternVideosCommand extends Command
{
    protected $signature = 'tools:parse-pattern-videos {--id=}';

    protected $description = 'Parse pattern videos';

    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info(message: 'Parsing patterns videos');

        $id = $this->option(key: 'id');

        $q = Pattern::query()
            ->whereHas(relation: 'meta', callback: fn($query) => $query->where('is_video_checked', false))
            ->with(relations: 'videos');

        if ($id) {
            $q->where('id', $id);
        }

        $count = $q->count();

        if ($count === 0) {
            $this->warn(message: "No patterns found to check for videos");

            return;
        }

        $this->info(message: "Found {$count} patterns to check for videos");

        $q->chunkById(
            count: 1,
            callback: function ($patterns): void {
                $pattern = $patterns->first();

                $this->info(message: "Processing pattern: {$pattern->id}");

                if ($pattern->videos->isNotEmpty()) {
                    $this->info(message: "Pattern {$pattern->id} has videos, skipping...");

                    $pattern->meta->update(['is_video_checked' => true]);

                    return;
                }

                $this->info(message: "Pattern {$pattern->id} has no videos, parsing...");

                $videos = $this->parsePatternVideo(pattern: $pattern);

                if ($videos === null) {
                    $this->error(message: "An error happened while parsing videos for pattern {$pattern->id}, skipping...");

                    return;
                }

                $videosToCreate = [];

                foreach ($videos as $video) {
                    $videosToCreate[] = new PatternVideo(attributes: [
                        'source' => $video['source'],
                        'source_identifier' => $video['video_id'],
                        'url' => match ($video['source']) {
                            VideoSourceEnum::YOUTUBE->value => "https://www.youtube.com/watch?v={$video['video_id']}",
                            VideoSourceEnum::VK->value => "https://vkvideo.ru/video{$video['video_id']}",
                            default => null,
                        },
                    ]);
                }

                try {
                    DB::beginTransaction();

                    if ($videosToCreate !== []) {
                        $videosToCreateCount = count(value: $videosToCreate);

                        $pattern->videos()->saveMany(models: $videosToCreate);

                        $this->success(message: "Created {$videosToCreateCount} videos for pattern {$pattern->id}");
                    }

                    $pattern->meta->update(['is_video_checked' => true]);

                    DB::commit();
                } catch (Exception $exception) {
                    DB::rollBack();

                    $this->error(
                        message: "Error saving videos for pattern {$pattern->id}: {$exception->getMessage()}",
                    );

                    return;
                }
            },
        );
    }

    protected function parsePatternVideo(Pattern $pattern): ?array
    {
        try {
            $content = $this->parserService->getClient()
                ->get(uri: $pattern->source_url)
                ->getBody()
                ->getContents();
        } catch (Exception $exception) {
            $this->error(
                message: "Error getting page content for pattern {$pattern->id}: {$exception->getMessage()}",
            );

            return null;
        }

        $youtubeVideoIds = $this->parserService->getYoutubeVideoIdsFromString($content);
        $vkVideoIds = $this->parserService->getVkVideoIdsFromString($content);

        $ytCount = count(value: $youtubeVideoIds);
        $vkCount = count(value: $vkVideoIds);

        if ($ytCount !== 0) {
            $this->info(
                message: "Found {$ytCount} YouTube video(s) for pattern {$pattern->id}",
            );
        }

        if ($vkCount !== 0) {
            $this->info(
                message: "Found {$vkCount} VK video(s) for pattern {$pattern->id}",
            );
        }

        $videos = [];

        foreach ($youtubeVideoIds as $videoId) {
            $videos[] = [
                'source' => VideoSourceEnum::YOUTUBE->value,
                'video_id' => $videoId,
            ];
        }

        foreach ($vkVideoIds as $videoId) {
            $videos[] = [
                'source' => VideoSourceEnum::VK->value,
                'video_id' => $videoId,
            ];
        }

        return $videos;
    }
}
