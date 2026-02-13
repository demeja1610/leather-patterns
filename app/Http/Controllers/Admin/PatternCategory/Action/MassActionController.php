<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Action;

use App\Enum\ActionEnum;
use App\Models\PatternCategory;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;
use App\Http\Requests\Admin\PatternCategory\MassActionRequest;

class MassActionController extends Controller
{
    public function __invoke(MassActionRequest $request): RedirectResponse
    {
        $action = ActionEnum::from($request->input('action'));
        $ids = $request->input('ids');

        return match ($action) {
            ActionEnum::MASS_DELETE => $this->deleteCategories(
                ids: array_map(
                    array: $ids,
                    callback: fn(string $id): int => (int) $id,
                ),
            ),

            default => redirect(status: Response::HTTP_BAD_REQUEST)->back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __('actions.admin.wrong_mass_action'),
                        type: NotificationTypeEnum::WARNING,
                    )
                ),
            ),
        };
    }

    protected function deleteCategories(array $ids): RedirectResponse
    {
        $result = PatternCategory::query()
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __('pattern_category.admin.success_mass_deleted', ['count' => $result]),
                    type: NotificationTypeEnum::WARNING,
                )
            ),
        );
    }
}
