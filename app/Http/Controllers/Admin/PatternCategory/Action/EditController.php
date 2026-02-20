<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Action;

use App\Models\PatternCategory;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Http\Requests\Admin\PatternCategory\EditRequest;
use App\Dto\SessionNotification\SessionNotificationListDto;

class EditController extends Controller
{
    public function __invoke($id, EditRequest $request): RedirectResponse
    {
        $data = array_merge(
            $request->validated(),
            [
                'remove_on_appear' => (bool) $request->input(key: 'remove_on_appear', default: false),
                'is_published' => (bool) $request->input(key: 'is_published', default: false),
            ],
        );

        $replaceToCount = 0;

        foreach ($data as $key => $value) {
            if (str_starts_with(needle: 'replace_', haystack: $key) && $value !== null) {
                $replaceToCount++;
            }
        }

        if ($data['remove_on_appear'] === true &&  $replaceToCount !== 0) {
            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_category.admin.cannot_remove_and_replace_same_time'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        $updated = PatternCategory::query()
            ->where(column: 'id', operator: $id)
            ->update(values: $data);

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated > 0
                    ? new SessionNotificationDto(
                        text: __(key: 'pattern_category.admin.updated', replace: ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_category.admin.failed_to_update', replace: ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }
}
