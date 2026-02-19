<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Action;

use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\PatternTag\EditRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;
use App\Models\PatternTag;

class EditController extends Controller
{
    public function __invoke($id, EditRequest $request): RedirectResponse
    {
        $data = array_merge(
            $request->validated(),
            [
                'remove_on_appear' => (bool) $request->get('remove_on_appear', false),
                'is_published' => (bool) $request->get('is_published', false),
            ]
        );

        if ($data['remove_on_appear'] === true && ($data['replace_id'] !== null || $data['replace_author_id'] !== null)) {
            return redirect()->back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.cannot_remove_and_replace_same_time'),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        if ($data['replace_id'] !== null && $data['replace_author_id'] !== null) {
            return redirect()->back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.cannot_replace_to_tag_and_author_same_time'),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $updated = PatternTag::query()
            ->where('id', $id)
            ->update($data);

        return redirect()->back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated > 0
                    ? new SessionNotificationDto(
                        text: __('pattern_tag.admin.updated', ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    :
                    new SessionNotificationDto(
                        text: __('pattern_tag.admin.failed_to_update', ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }
}
