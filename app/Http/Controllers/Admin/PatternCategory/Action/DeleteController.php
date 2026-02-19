<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Action;

use App\Models\PatternCategory;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class DeleteController extends Controller
{
    public function __invoke($id): RedirectResponse
    {
        $category = $this->getPatternCategory($id);

        if ($category === null) {
            return redirect()->back();
        }

        if ($category->remove_on_appear === true || $category->replace_id !== null) {
            return redirect()->back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_category.admin.category_needed_for_replace_or_remove', ['name' => $category->name]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $category->loadCount('patterns');

        if ($category->patterns_count !== 0) {
            return redirect()->back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_category.admin.patterns_not_empty', [
                            'name' => $category->name,
                            'count' => $category->patterns_count
                        ]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $category->loadCount('replacementFor');

        if ($category->replacement_for_count !== 0) {
            return redirect()->back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('pattern_category.admin.category_is_replacement_for', [
                            'name' => $category->name,
                            'count' => $category->replacement_for_count
                        ]),
                        type: NotificationTypeEnum::ERROR,
                    )
                ),
            );
        }

        $deleted = $category->delete();

        return redirect()->back()->with(
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

        return $q->first();
    }
}
