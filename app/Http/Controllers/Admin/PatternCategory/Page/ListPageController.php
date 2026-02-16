<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Models\PatternCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PatternCategory\ListRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ListPageController extends Controller
{
    protected array $activeFilters = [];

    public function __invoke(ListRequest $request)
    {
        $categories = $this->getCategories($request);

        return view('pages.admin.pattern-category.list', [
            'activeFilters' => $this->activeFilters,
            'categories' => $categories,
        ]);
    }

    protected function getCategories(ListRequest &$request)
    {
        $cursor = $request->get('cursor', null);

        $q = PatternCategory::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->withCount('patterns');

        return $q->orderBy('id', 'desc')->cursorPaginate(
            perPage: 30,
            cursor: $cursor,
        );
    }

    protected function applyFilters(ListRequest &$request, Builder &$query): void
    {
        $id = $request->get('id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where('id', $id);
        }

        $name = $request->get('name');

        if ($name !== null) {
            $this->activeFilters['name'] = $name;

            $query->where('name', 'LIKE', "%{$name}%");
        }

        $olderThanStr = $request->get('older_than');

        if ($olderThanStr !== null) {
            $olderThan = Carbon::parse($olderThanStr);

            $this->activeFilters['older_than'] = $olderThan;

            $query->where('created_at', '<', $olderThan);
        }

        $newerThanStr = $request->get('newer_than');

        if ($newerThanStr !== null) {
            $newerThan = Carbon::parse($newerThanStr);

            $this->activeFilters['newer_than'] = $newerThan;

            $query->where('created_at', '>', $newerThan);
        }
    }
}
