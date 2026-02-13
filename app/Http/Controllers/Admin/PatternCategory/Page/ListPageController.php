<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use Illuminate\Http\Request;
use App\Models\PatternCategory;
use App\Http\Controllers\Controller;

class ListPageController extends Controller
{
    public function __invoke(Request $request)
    {
        $categories = $this->getCategories($request);

        return view('pages.admin.pattern-category.list', [
            'categories' => $categories,
        ]);
    }

    protected function getCategories(Request &$request)
    {
        $cursor = $request->get('cursor', null);

        $q = PatternCategory::query();

        $q->withCount('patterns');

        return $q->orderBy('id', 'desc')->cursorPaginate(
            perPage: 30,
            cursor: $cursor,
        );
    }
}
