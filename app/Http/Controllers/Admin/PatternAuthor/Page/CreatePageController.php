<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Page;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class CreatePageController extends Controller
{
    public function __invoke(): View
    {
        return view(view: 'pages.admin.pattern-author.create');
    }
}
