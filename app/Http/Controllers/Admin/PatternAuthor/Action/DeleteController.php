<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Action;

use App\Models\PatternAuthor;
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
        $author = $this->getPatternAuthor(id: $id);

        if (!$author instanceof PatternAuthor) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        if ($author->isDeletable()) {
            $deleted = $author->delete();
        } else {
            return back()->with(
                key: 'notifications',
                value: new SessionNotificationListDto(
                    new SessionNotificationDto(
                        text: __(key: 'pattern_author.admin.author_isnt_deletable', replace: [
                            'name' => $author->name,
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
                        text: __(key: 'pattern_author.admin.single_delete_success', replace: ['name' => $author->name]),
                        type: NotificationTypeEnum::SUCCESS,
                    )
                    : new SessionNotificationDto(
                        text: __(key: 'pattern_author.admin.single_failed_to_delete', replace: ['name' => $author->name]),
                        type: NotificationTypeEnum::ERROR,
                    ),
            ),
        );
    }

    protected function getPatternAuthor($id): ?PatternAuthor
    {
        $q =  PatternAuthor::query();

        $q->where("id", $id);

        $q->withCount(relations: [
            'patterns',
            'replacementFor',
            'replacementForTags',
        ]); // optimization for isDeleted method

        return $q->first();
    }
}
