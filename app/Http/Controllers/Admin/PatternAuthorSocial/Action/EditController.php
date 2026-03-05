<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthorSocial\Action;

use App\Enum\SocialTypeEnum;
use App\Enum\NotificationTypeEnum;
use App\Models\PatternAuthorSocial;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Dto\SessionNotification\SessionNotificationDto;
use App\Dto\SessionNotification\SessionNotificationListDto;
use App\Http\Requests\Admin\PatternAuthorSocial\EditRequest;

class EditController extends Controller
{
    public function __invoke($id, EditRequest $request): RedirectResponse
    {
        $social = PatternAuthorSocial::query()->where('id', $id)->first();

        if (!$social instanceof PatternAuthorSocial) {
            abort(Response::HTTP_NOT_FOUND);
        }

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

        $updated = $social->update($data);

        return back()->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                $updated !== false
                    ? new SessionNotificationDto(
                        text: __(key: 'pattern_author_social.admin.updated', replace: ['id' => $id]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_author_social.admin.failed_to_update', replace: ['id' => $id]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }
}
