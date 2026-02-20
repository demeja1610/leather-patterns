<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Page;

use App\Models\PatternCategory;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\PatternCategory\ListRequest;
use Carbon\Carbon;

class ListPageController extends Controller
{
    protected array $activeFilters = [];

    public function __invoke(ListRequest $request): View
    {
        $categories = $this->getCategories(request: $request);

        return view(view: 'pages.admin.pattern-category.list', data: [
            'activeFilters' => $this->activeFilters,
            'categories' => $categories,
        ]);
    }

    protected function getCategories(ListRequest &$request)
    {
        $cursor = $request->input(key: 'cursor');

        $q = PatternCategory::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->withCount(relations: [
            'patterns',
            'replacementFor',
            'replacementForTags'
        ]);

        $q->with(relations: 'replacement');

        return $q->orderBy('id', 'desc')->cursorPaginate(
            perPage: 30,
            cursor: $cursor,
        )->withQueryString();
    }

    protected function applyFilters(ListRequest &$request, Builder &$query): void
    {
        $id = $request->input(key: 'id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where(column: 'id', operator: $id);
        }

        $name = $request->input(key: 'name');

        if ($name !== null) {
            $this->activeFilters['name'] = $name;

            $query->where(column: 'name', operator: 'LIKE', value: "%{$name}%");
        }

        $olderThanStr = $request->input(key: 'older_than');

        if ($olderThanStr !== null) {
            $olderThan = Carbon::parse(time: $olderThanStr);

            $this->activeFilters['older_than'] = $olderThan;

            $query->where(column: 'created_at', operator: '<', value: $olderThan);
        }

        $newerThanStr = $request->input(key: 'newer_than');

        if ($newerThanStr !== null) {
            $newerThan = Carbon::parse(time: $newerThanStr);

            $this->activeFilters['newer_than'] = $newerThan;

            $query->where(column: 'created_at', operator: '>', value: $newerThan);
        }

        $hasPatterns = $request->input(key: 'has_patterns');

        if ($hasPatterns !== null) {
            $this->activeFilters['has_patterns'] = (bool) $hasPatterns;

            if ((bool) $hasPatterns) {
                $query->whereHas(relation: 'patterns');
            } else {
                $query->whereDoesntHave(relation: 'patterns');
            }
        }

        $isPublished = $request->input(key: 'is_published');

        if ($isPublished !== null) {
            $this->activeFilters['is_published'] = (bool) $isPublished;

            if ((bool) $isPublished) {
                $query->where(column: 'is_published', operator: true);
            } else {
                $query->where(column: 'is_published', operator: false);
            }
        }

        $hasReplacement = $request->input(key: 'has_replacement');

        if ($hasReplacement !== null) {
            $this->activeFilters['has_replacement'] = (bool) $hasReplacement;

            if ((bool) $hasReplacement) {
                $query->whereNotNull('replace_id');
            } else {
                $query->whereNull('replace_id');
            }
        }

        $removeOnAppear = $request->input(key: 'remove_on_appear');

        if ($removeOnAppear !== null) {
            $this->activeFilters['remove_on_appear'] = (bool) $removeOnAppear;

            if ((bool) $removeOnAppear) {
                $query->where(column: 'remove_on_appear', operator: true);
            } else {
                $query->where(column: 'remove_on_appear', operator: false);
            }
        }
    }
}
