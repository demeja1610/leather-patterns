<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Action;

use App\Models\PatternTag;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\PatternTag\CreateRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class CreateController extends Controller
{
    public function __invoke(CreateRequest $request): RedirectResponse
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

        if ($data['remove_on_appear'] === true && $replaceToCount !== 0) {
            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_tag.admin.cannot_remove_and_replace_same_time'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        if ($replaceToCount > 1) {
            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_tag.admin.cannot_replace_to_multiple'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        $tag = PatternTag::query()->create(attributes: $data);

        return to_route(route: 'admin.page.pattern-tag.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern_tag.admin.created', replace: ['name' => $tag->name]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }
}
