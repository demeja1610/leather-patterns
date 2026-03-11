<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthorSocial\Action;

use App\Enum\SocialTypeEnum;
use App\Models\PatternAuthor;
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

        $authorId = $request->input('author_id');

        $author = $this->getSelectedAuthor($authorId);

        if ($urlSocialType === null) {
            $request->session()->flash('selected_author', $author);

            throw ValidationException::withMessages(messages: [
                'url' => __('validation.bad_url'),
            ]);
        }

        $data['type'] = $urlSocialType->value;

        $social = PatternAuthorSocial::query()->create(attributes: $data);

        return to_route(route: 'admin.page.pattern-author-socials.list')->with(
            key: 'notifications',
            value: new SessionNotificationListDto(
                new SessionNotificationDto(
                    text: __(key: 'pattern_author_social.admin.created', replace: ['type' => $social->type->value]),
                    type: NotificationTypeEnum::SUCCESS,
                ),
            ),
        );
    }

    protected function getSelectedAuthor($id): ?PatternAuthor
    {
        return PatternAuthor::query()
            ->where('id', $id)
            ->select([
                'id',
                'name'
            ])
            ->first();
    }
}
