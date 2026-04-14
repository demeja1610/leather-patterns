<?php

namespace App\Http\Controllers\Admin\PatternFile\Action;

use App\Models\PatternFile;
use Illuminate\Http\Request;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class DeleteController extends Controller
{
    public function __invoke($id, Request $request)
    {
        $file = PatternFile::find($id);

        if (!$file instanceof PatternFile) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        $deleted =  $file->delete();

        return $request->wantsJson()
            ? response(status: Response::HTTP_OK)
            : back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    $deleted > 0
                        ? new SessionNotificationDto(
                            text: __(key: 'pattern_file.admin.single_delete_success', replace: ['id' => $file->id]),
                            type: NotificationTypeEnum::SUCCESS,
                        )
                        : new SessionNotificationDto(
                            text: __(key: 'pattern_file.admin.single_failed_to_delete', replace: ['id' => $file->id]),
                            type: NotificationTypeEnum::ERROR,
                        ),
                ),
            );;
    }
}
