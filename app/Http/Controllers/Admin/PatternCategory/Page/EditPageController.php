<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Models\PatternCategory;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $category = PatternCategory::find($id);

        if (!$category) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        return view('pages.admin.pattern-category.edit', compact([
            'category'
        ]));
    }
}
