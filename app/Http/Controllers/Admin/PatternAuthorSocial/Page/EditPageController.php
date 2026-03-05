<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthorSocial\Page;

use App\Enum\SocialTypeEnum;
use App\Models\PatternAuthorSocial;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $social = $this->getAuthorSocial(id: $id);

        if (!$social instanceof PatternAuthorSocial) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        $this->loadRelations(social: $social);

        $types = SocialTypeEnum::cases();

        return view(view: 'pages.admin.pattern-author-social.edit', data: [
            'social' => $social,
            'types' => $types,
        ]);
    }

    protected function getAuthorSocial($id): ?PatternAuthorSocial
    {
        return PatternAuthorSocial::query()->find(id: $id);
    }

    protected function loadRelations(PatternAuthorSocial &$social): void
    {
        $social->load(relations: 'author');
    }
}
