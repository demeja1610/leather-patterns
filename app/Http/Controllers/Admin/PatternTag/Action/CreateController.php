<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Action;

use App\Models\PatternTag;
use App\Enum\NotificationTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\PatternTag\CreateRequest;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;
use App\Models\PatternAuthor;
use App\Models\PatternCategory;

class CreateController extends Controller
{
    public function __invoke(CreateRequest $request): RedirectResponse
    {
        $data = array_merge(
            $request->validated(),
            [
                'remove_on_appear' => (bool) $request->input(key: 'remove_on_appear', default: false),
                'is_published' => (bool) $request->input(key: 'is_published', default: false),
            ],
        );

        $replaceToCount = 0;

        foreach ($data as $key => $value) {
            if (str_starts_with(haystack: (string) $key, needle: 'replace_') && $value !== null) {
                $replaceToCount++;
            }
        }

        $replaceId = $request->input('replace_id');

        if ($replaceId !== null) {
            $replace = PatternTag::query()->where('id', $replaceId)->select(['id', 'name'])->first();

            if ($replace instanceof PatternTag) {
                $request->session()->flash('replace_name', $replace->name);
            }
        }

        $replaceAuthorId = $request->input('replace_author_id');

        if ($replaceAuthorId !== null) {
            $replaceAuthor = PatternAuthor::query()->where('id', $replaceAuthorId)->select(['id', 'name'])->first();

            if ($replaceAuthor instanceof PatternAuthor) {
                $request->session()->flash('replace_author_name', $replaceAuthor->name);
            }
        }

        $replaceCategoryId = $request->input('replace_category_id');

        if ($replaceCategoryId !== null) {
            $replaceCategory = PatternCategory::query()->where('id', $replaceCategoryId)->select(['id', 'name'])->first();

            if ($replaceCategory instanceof PatternCategory) {
                $request->session()->flash('replace_category_name', $replaceCategory->name);
            }
        }

        if ($data['remove_on_appear'] === true && $replaceToCount !== 0) {
            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_tag.admin.cannot_remove_and_replace_same_time'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        if ($replaceToCount > 1) {
            return back()->withInput()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_tag.admin.cannot_replace_to_multiple'),
                        type: NotificationTypeEnum::ERROR,
                    ),
                ),
            );
        }

        $tag = PatternTag::query()->create(attributes: $data);

        return to_route(route: 'admin.page.pattern-tag.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern_tag.admin.created', replace: ['name' => $tag->name]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }
}
