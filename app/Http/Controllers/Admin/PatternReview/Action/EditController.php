<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternReview\Action;

use App\Models\PatternReview;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\PatternReview\EditRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class EditController extends Controller
{
    public function __invoke($id, EditRequest $request): RedirectResponse
    {
        $review = PatternReview::query()
            ->where('id', $id)
            ->first();

        if (!$review instanceof PatternReview) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $data = array_merge(
            $request->validated(),
            [
                'is_approved' => (bool) $request->input(key: 'is_approved', default: false),
            ],
        );

        if (isset($data['comment'])) {
            $data['comment'] = trim($data['comment']);
        }

        $updated = $review->update($data);

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated !== false
                    ? new SessionNotificationDto(
                        text: __(key: 'pattern_review.admin.updated', replace: ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_review.admin.failed_to_update', replace: ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }
}
