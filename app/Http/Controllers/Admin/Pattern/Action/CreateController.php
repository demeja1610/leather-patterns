<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Action;

use App\Models\Pattern;
use App\Enum\NotificationTypeEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\Pattern\CreateRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;

class CreateController extends Controller
{
    public function __invoke(CreateRequest $request): RedirectResponse
    {
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

            $pattern = Pattern::query()->create(attributes: $data);

            if ($categoryIds !== []) {
                $pattern->categories()->attach($categoryIds);
            }

            if ($tagIds !== []) {
                $pattern->tags()->attach($tagIds);
            }

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
                        text: __(key: 'pattern.admin.error_while_creating'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        return to_route(route: 'admin.page.patterns.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern.admin.created', replace: ['title' => $pattern->title]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }
}
