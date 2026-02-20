<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Action;

use App\Models\PatternCategory;
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
        $category = $this->getPatternCategory($id);

        if (!$category instanceof PatternCategory) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        if ($category->isDeletable()) {
            $deleted = $category->delete();
        } else {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_category.admin.category_isnt_deletable', [
                            'name' => $category->name,
                        ]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $deleted > 0
                    ? new SessionNotificationDto(
                        text: __('pattern_category.admin.single_delete_success', ['name' => $category->name]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    :
                    new SessionNotificationDto(
                        text: __('pattern_category.admin.single_failed_to_delete', ['name' => $category->name]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function getPatternCategory($id): ?PatternCategory
    {
        $q =  PatternCategory::query();

        $q->where("id", $id);

        $q->withCount([
            'patterns',
            'replacementFor',
        ]); // optimization for isDeleted method

        return $q->first();
    }
}
