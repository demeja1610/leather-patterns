<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Action;

use App\Models\PatternAuthor;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Http\Requests\Admin\PatternAuthor\CreateRequest;
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
            if (str_starts_with(haystack: (string) $key, needle: 'replace_') && $value !== null) {
                $replaceToCount++;
            }
        }

        if ($data['remove_on_appear'] === true &&  $replaceToCount !== 0) {
            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_author.admin.cannot_remove_and_replace_same_time'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        $author = PatternAuthor::query()->create(attributes: $data);

        return to_route(route: 'admin.page.pattern-author.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern_author.admin.created', replace: ['name' => $author->name]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }
}
