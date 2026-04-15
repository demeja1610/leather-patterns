<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Action;

use App\Models\Pattern;
use App\Enum\PatternSourceEnum;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use App\Jobs\Parser\ParsePatternsJob;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class UpdateFromSourceController extends Controller
{
    public function __invoke($id): RedirectResponse
    {
        $pattern = $this->getPattern(id: $id);

        if (!$pattern instanceof Pattern) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        if ($pattern->source === PatternSourceEnum::LOCAL) {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern.admin.only_not_local_allowed', replace: ['id' => $pattern->id]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        dispatch(new ParsePatternsJob($pattern->id));

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern.admin.updating_from_source', replace: ['id' => $pattern->id]),
                    type: NotificationTypeEnum::SUCCESS,
                )
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
