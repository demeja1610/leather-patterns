<?php

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
        protected ParserServiceInterface $parserService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Parsing patterns videos');

        $id = $this->option('id');

        $q = Pattern::query()
            ->whereHas('meta', fn($query) => $query->where('is_video_checked', false))
            ->with('videos');

        if ($id) {
            $q->where('id', $id);
        }

        $count = $q->count();

        if ($count === 0) {
            $this->warn("No patterns found to check for videos");

            return;
        }

        $this->info("Found $count patterns to check for videos");

        $q->chunkById(
            count: 1,
            callback: function ($patterns) {
                $pattern = $patterns->first();

                $this->info("Processing pattern: {$pattern->id}");

                if ($pattern->videos->isNotEmpty()) {
                    $this->info("Pattern {$pattern->id} has videos, skipping...");

                    $pattern->meta->update(['is_video_checked' => true]);

                    return;
                }

                $this->info("Pattern {$pattern->id} has no videos, parsing...");

                $videos = $this->parsePatternVideo($pattern);

                if ($videos === null) {
                    $this->error("An error happened while parsing videos for pattern {$pattern->id}, skipping...");

                    return;
                }

                $videosToCreate = [];

                foreach ($videos as $video) {
                    $videosToCreate[] = new PatternVideo([
                        'source' => $video['source'],
                        'source_identifier' => $video['video_id'],
                        'url' => match ($video['source']) {
                            VideoSourceEnum::YOUTUBE->value => "https://www.youtube.com/watch?v={$video['video_id']}",
                            VideoSourceEnum::VK->value => "https://vkvideo.ru/video{$video['video_id']}",
                            default => null,
                        }
                    ]);
                }

                try {
                    DB::beginTransaction();

                    if ($videosToCreate !== []) {
                        $videosToCreateCount = count($videosToCreate);

                        $pattern->videos()->saveMany($videosToCreate);

                        $this->success("Created {$videosToCreateCount} videos for pattern {$pattern->id}");
                    }

                    $pattern->meta->update(['is_video_checked' => true]);

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();

                    $this->error("Error saving videos for pattern {$pattern->id}: {$e->getMessage()}");

                    return;
                }
            }
        );
    }

    protected function parsePatternVideo(Pattern $pattern): ?array
    {
        try {
            $content = $this->parserService->getClient()
                ->get($pattern->source_url)
                ->getBody()
                ->getContents();
        } catch (Exception $e) {
            $this->error("Error getting page content for pattern {$pattern->id}: {$e->getMessage()}");

            return null;
        }

        $youtubeVideoIds = $this->parserService->getYoutubeVideoIdsFromString($content);
        $vkVideoIds = $this->parserService->getVkVideoIdsFromString($content);

        $ytCount = count($youtubeVideoIds);
        $vkCount = count($vkVideoIds);

        if ($ytCount) {
            $this->info("Found $ytCount YouTube video(s) for pattern {$pattern->id}");
        }

        if ($vkCount) {
            $this->info("Found $vkCount VK video(s) for pattern {$pattern->id}");
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
