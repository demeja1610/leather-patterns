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
        $data = $request->validated();

        $updated = PatternCategory::query()
            ->where('id', $id)
            ->update($data);

        return redirect()->back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated > 0
                    ? new SessionNotificationDto(
                        text: __('pattern_category.admin.updated', ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    :
                    new SessionNotificationDto(
                        text: __('pattern_category.admin.failed_to_update', ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }
}
