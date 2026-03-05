<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthorSocial\Action;

use App\Enum\SocialTypeEnum;
use App\Enum\NotificationTypeEnum;
use App\Models\PatternAuthorSocial;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;
use App\Http\Requests\Admin\PatternAuthorSocial\CreateRequest;

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

        $urlSocialType = SocialTypeEnum::getFromUrl($data['url']);

        if ($urlSocialType === null) {
            throw ValidationException::withMessages(messages: [
                'url' => __('validation.bad_url'),
            ]);
        }

        $type = SocialTypeEnum::tryFrom($data['type']);

        if ($type !== $urlSocialType) {
            throw ValidationException::withMessages(messages: [
                'url' => __('pattern_author_social.admin.url_type_mismatch'),
                'type' => __('pattern_author_social.admin.url_type_mismatch'),
            ]);
        }

        $social = PatternAuthorSocial::query()->create(attributes: $data);

        return to_route(route: 'admin.page.pattern-author-social.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern_author_social.admin.created', replace: ['type' => $social->type->value]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }
}
