<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternReview\Action;

use App\Models\PatternReview;
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
        $review = $this->getPatternReview(id: $id);

        if (!$review instanceof PatternReview) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        if ($review->isDeletable()) {
            $deleted = $review->delete();
        } else {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_review.admin.review_isnt_deletable', replace: [
                            'id' => $review->id,
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
                        text: __(key: 'pattern_review.admin.single_delete_success', replace: ['id' => $review->id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_review.admin.single_failed_to_delete', replace: ['name' => $review->id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function getPatternReview($id): ?PatternReview
    {
        $q =  PatternReview::query();

        $q->where("id", $id);

        return $q->first();
    }
}
