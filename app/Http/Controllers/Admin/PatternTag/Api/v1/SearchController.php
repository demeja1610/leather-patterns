<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternTag\Api\v1;

use App\Models\PatternTag;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Resources\Api\PatternTag\PatternTagResource;
use App\Http\Requests\Admin\PatternTag\Api\v1\SearchRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): AnonymousResourceCollection
    {
        $patternTags = $this->searchPatternTags(
            request: $request,
        );

        return PatternTagResource::collection(resource: $patternTags);
    }

    protected function searchPatternTags(SearchRequest &$request): Collection
    {
        $q = $this->getBasePatternTagQuery();

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

    protected function getBasePatternTagQuery(): Builder
    {
        return PatternTag::query();
    }

    protected function applyFilters(Builder &$query, SearchRequest &$request): void
    {
        $q = $request->input(key: 'q');

        $query->where('name', 'LIKE', "%{$q}%");
    }
}
