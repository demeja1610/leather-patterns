<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternCategory\Api\v1;

use App\Models\PatternCategory;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Admin\PatternCategory\Api\v1\SearchRequest;
use App\Http\Resources\Api\PatternCategory\PatternCategoryResource;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): AnonymousResourceCollection
    {
        $patternCategories = $this->searchPatternCategories(
            request: $request,
        );

        return PatternCategoryResource::collection(resource: $patternCategories);
    }

    protected function searchPatternCategories(SearchRequest &$request): Collection
    {
        $q = $this->getBasePatternCategoryQuery();

        $this->applyFilters(
            query: $q,
            request: $request,
        );

        $q->select([
            'id',
            'name',
        ]);

        return $q->orderBy('name', 'asc')->get();
    }

    protected function getBasePatternCategoryQuery(): Builder
    {
        return PatternCategory::query();
    }

    protected function applyFilters(Builder &$query, SearchRequest &$request): void
    {
        $q = $request->input(key: 'q');

        $query->where('name', 'LIKE', "%{$q}%");

        $exceptId = $request->input('except_id');

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
    }
}
