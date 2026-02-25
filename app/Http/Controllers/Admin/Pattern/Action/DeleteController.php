<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Action;

use App\Models\Pattern;
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
        $pattern = $this->getPattern(id: $id);

        if (!$pattern instanceof Pattern) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        if ($pattern->isDeletable()) {
            $deleted = $pattern->delete();
        } else {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern.admin.pattern_isnt_deletable', replace: [
                            'title' => $pattern->title,
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
                        text: __(key: 'pattern.admin.single_delete_success', replace: ['title' => $pattern->title]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern.admin.single_failed_to_delete', replace: ['title' => $pattern->title]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function getPattern($id): ?Pattern
    {
        $q =  Pattern::query();

        $q->where("id", $id);

        return $q->first();
    }
}
