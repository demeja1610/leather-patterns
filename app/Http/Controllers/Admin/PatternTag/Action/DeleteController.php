<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Action;

use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;
use App\Models\PatternTag;

class DeleteController extends Controller
{
    public function __invoke($id): RedirectResponse
    {
        $tag = $this->getPatternTag($id);

        if ($tag === null) {
            return redirect()->back();
        }

        if ($tag->remove_on_appear === true || $tag->replace_id !== null || $tag->replace_author_id !== null) {
            return redirect()->back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.tag_needed_for_replace_or_remove', ['name' => $tag->name]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $tag->loadCount('patterns');

        if ($tag->patterns_count !== 0) {
            return redirect()->back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.patterns_not_empty', [
                            'name' => $tag->name,
                            'count' => $tag->patterns_count
                        ]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $tag->loadCount('replacementFor');

        if ($tag->replacement_for_count !== 0) {
            return redirect()->back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.tag_is_replacement_for', [
                            'name' => $tag->name,
                            'count' => $tag->replacement_for_count
                        ]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $deleted = $tag->delete();

        return redirect()->back()->with(
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

        return $q->first();
    }
}
