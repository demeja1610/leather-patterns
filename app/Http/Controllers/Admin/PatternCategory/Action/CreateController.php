<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Action;

use App\Models\PatternCategory;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Http\Requests\Admin\PatternCategory\CreateRequest;
use App\Dto\SessionNotification\SessionNotificationListDto;

class CreateController extends Controller
{
    public function __invoke(CreateRequest $request): RedirectResponse
    {
        $data = array_merge(
            $request->validated(),
            [
                'remove_on_appear' => (bool) $request->get(key: 'remove_on_appear', default: false),
                'is_published' => (bool) $request->get(key: 'is_published', default: false),
            ]
        );

        if ($data['remove_on_appear'] === true && $data['replace_id'] !== null) {
            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_category.admin.cannot_remove_and_replace_same_time'),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $category = PatternCategory::query()->create(attributes: $data);

        return to_route(route: 'admin.page.pattern-category.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern_category.admin.created', replace: ['name' => $category->name]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }
}
