<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternVideo\Action;

use App\Models\PatternVideo;
use App\Enum\NotificationTypeEnum;
use App\Dto\Parser\Pattern\VideoDto;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use App\Interfaces\Services\ParserServiceInterface;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Http\Requests\Admin\PatternVideo\CreateRequest;
use App\Dto\SessionNotification\SessionNotificationListDto;

class CreateController extends Controller
{
    public function __construct(
        protected readonly ParserServiceInterface $parserService,
    ) {}

    public function __invoke(CreateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $url = $data['url'];
        $patternId = (int) $data['pattern_id'];

        $videos = $this->parserService->getVideosFromString($url);

        if ($videos->count() === 0) {
            throw ValidationException::withMessages(messages: [
                'url' => __('pattern_video.errors.unknown_source_or_wrong_url'),
            ]);
        }

        if ($videos->count() > 1) {
            throw ValidationException::withMessages(messages: [
                'url' => __('pattern_video.errors.only_single_video_allowed'),
            ]);
        }

        $video = $videos->getItems()[0];

        $videoExists = $this->isVideoExists($patternId, $video);

        if ($videoExists === true) {
            throw ValidationException::withMessages(messages: [
                'url' => __('pattern_video.errors.alredy_exists_for_pattern'),
            ]);
        }

        $patternVideo = PatternVideo::query()->create([
            'source_identifier' => $video->getSourceIdentifier(),
            'pattern_id' => $patternId,
            'url' => $video->getUrl(),
            'source' => $video->getSource()->value,
        ]);

        return to_route(route: 'admin.page.pattern-videos.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern_video.admin.created', replace: ['url' => $patternVideo->url]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }

    protected function isVideoExists(int $patternId, VideoDto $video): bool
    {
        return PatternVideo::query()
            ->where('pattern_id', $patternId)
            ->where('source_identifier', $video->getSourceIdentifier())
            ->exists();
    }
}
