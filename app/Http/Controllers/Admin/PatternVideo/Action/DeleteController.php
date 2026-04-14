<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternVideo\Action;

use App\Models\PatternVideo;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class DeleteController extends Controller
{
    public function __invoke($id): RedirectResponse
    {
        $video = $this->getPatternVideo(id: $id);

        if (!$video instanceof PatternVideo) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        if ($video->isDeletable()) {
            $deleted = $video->delete();
        } else {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_video.admin.video_isnt_deletable', replace: [
                            'url' => $video->url,
                        ]),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $deleted > 0
                    ? new SessionNotificationDto(
                        text: __(key: 'pattern_video.admin.single_delete_success', replace: ['url' => $video->url]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_video.admin.single_failed_to_delete', replace: ['url' => $video->url]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function getPatternVideo($id): ?PatternVideo
    {
        $q =  PatternVideo::query();

        $q->where("id", $id);

        return $q->first();
    }
}
