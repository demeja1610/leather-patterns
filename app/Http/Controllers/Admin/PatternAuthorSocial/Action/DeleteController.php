<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthorSocial\Action;

use App\Enum\NotificationTypeEnum;
use App\Models\PatternAuthorSocial;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class DeleteController extends Controller
{
    public function __invoke($id): RedirectResponse
    {
        $social = $this->getSocial(id: $id);

        if (!$social instanceof PatternAuthorSocial) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        if ($social->isDeletable()) {
            $deleted = $social->delete();
        } else {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_author_social.admin.social_isnt_deletable', replace: [
                            'id' => $social->id,
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
                        text: __(key: 'pattern_author_social.admin.single_delete_success', replace: ['id' => $social->id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_author_social.admin.single_failed_to_delete', replace: ['id' => $social->id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function getSocial($id): ?PatternAuthorSocial
    {
        $q =  PatternAuthorSocial::query();

        $q->where("id", $id);

        return $q->first();
    }
}
