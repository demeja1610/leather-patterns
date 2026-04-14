<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternVideo\Action;

use App\Models\PatternVideo;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Interfaces\Services\ParserServiceInterface;
use App\Http\Requests\Admin\PatternVideo\EditRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class EditController extends Controller
{
    public function __construct(
        protected readonly ParserServiceInterface $parserService,
    ) {}

    public function __invoke($id, EditRequest $request): RedirectResponse
    {
        $patternVideo = PatternVideo::query()->where('id', $id)->first();

        if (!$patternVideo instanceof PatternVideo) {
            abort(Response::HTTP_NOT_FOUND);
        }

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

        if ($video->getUrl() !== $patternVideo->url) {
            $patternVideo->url = $url;
            $patternVideo->source = $video->getSource();
            $patternVideo->source_identifier = $video->getSourceIdentifier();
        }

        if ($patternId !== $patternVideo->pattern_id) {
            $patternVideo->pattern_id = $patternId;
        }

        $videoExists = $this->isVideoExists($patternVideo);

        if ($videoExists === true) {
            throw ValidationException::withMessages(messages: [
                'url' => __('pattern_video.errors.alredy_exists_for_pattern'),
            ]);
        }

        $updated = $patternVideo->save();

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated !== false
                    ? new SessionNotificationDto(
                        text: __(key: 'pattern_video.admin.updated', replace: ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_video.admin.failed_to_update', replace: ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function isVideoExists(PatternVideo &$patternVideo): bool
    {
        return PatternVideo::query()
            ->where('pattern_id', $patternVideo->pattern_id)
            ->where('source_identifier', $patternVideo->source_identifier)
            ->where('id', '!=', $patternVideo->id)
            ->exists();
    }
}
