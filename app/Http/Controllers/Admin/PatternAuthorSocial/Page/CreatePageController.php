<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthorSocial\Page;

use App\Enum\SocialTypeEnum;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class CreatePageController extends Controller
{
    public function __invoke(): View
    {
        $types = SocialTypeEnum::cases();

        return view('pages.admin.pattern-author-social.create', [
            'types' => $types,
        ]);
    }
}
