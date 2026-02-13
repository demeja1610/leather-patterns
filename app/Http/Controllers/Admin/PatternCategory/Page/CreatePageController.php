<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Http\Controllers\Controller;

class CreatePageController extends Controller
{
    public function __invoke()
    {
        return view('pages.admin.pattern-category.create');
    }
}
