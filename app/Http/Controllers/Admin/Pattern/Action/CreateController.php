<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Action;

use App\Models\Pattern;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\Pattern\CreateRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class CreateController extends Controller
{
    public function __invoke(CreateRequest $request): RedirectResponse
    {
        $data = array_merge(
            $request->validated(),
            [
                'is_published' => (bool) $request->input(key: 'is_published', default: false),
            ],
        );

        $pattern = Pattern::query()->create(attributes: $data);

        return to_route(route: 'admin.page.patterns.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern.admin.created', replace: ['title' => $pattern->title]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }
}
