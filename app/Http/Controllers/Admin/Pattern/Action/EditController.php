<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Action;

use App\Models\Pattern;
use App\Enum\NotificationTypeEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\Pattern\EditRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class EditController extends Controller
{
    public function __invoke($id, EditRequest $request): RedirectResponse
    {
        $pattern = Pattern::query()->where('id', $id)->first();

        if (!$pattern instanceof Pattern) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $data = array_merge(
            $request->validated(),
            [
                'is_published' => (bool) $request->input(key: 'is_published', default: false),
            ],
        );

        $categoryIds = [];

        if (isset($data['category_id'])) {
            $categoryIds = $data['category_id'];

            unset($data['category_id']);
        }

        $tagIds = [];

        if (isset($data['tag_id'])) {
            $tagIds = $data['tag_id'];

            unset($data['tag_id']);
        }

        try {
            DB::beginTransaction();

            $updated = $pattern->update($data);

            $pattern->categories()->sync($categoryIds);

            $pattern->tags()->sync($tagIds);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            $request->flashSelectedAuthor();
            $request->flashSelectedCategories();
            $request->flashSelectedTags();

            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern.admin.error_while_updating'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated > 0
                    ? new SessionNotificationDto(
                        text: __(key: 'pattern.admin.updated', replace: ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern.admin.failed_to_update', replace: ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }
}
