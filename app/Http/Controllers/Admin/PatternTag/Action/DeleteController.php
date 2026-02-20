<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Action;

use App\Models\PatternTag;
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
        $tag = $this->getPatternTag($id);

        if (!$tag instanceof PatternTag) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        if ($tag->isDeletable()) {
            $deleted = $tag->delete();
        } else {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.tag_isnt_deletable', [
                            'name' => $tag->name,
                        ]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $deleted > 0
                    ? new SessionNotificationDto(
                        text: __('pattern_tag.admin.single_delete_success', ['name' => $tag->name]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    :
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.single_failed_to_delete', ['name' => $tag->name]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function getPatternTag($id): ?PatternTag
    {
        $q =  PatternTag::query();

        $q->where("id", $id);

        $q->withCount([
            'patterns',
            'replacementFor',
        ]); // optimization for isDeleted method

        return $q->first();
    }
}
