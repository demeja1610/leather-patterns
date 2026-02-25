<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pattern\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Resources\Api\Pattern\PatternResource;
use App\Http\Requests\Admin\Pattern\Api\v1\SearchRequest;
use App\Models\Pattern;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): AnonymousResourceCollection
    {
        $patterns = $this->searchPattern(
            request: $request,
        );

        return PatternResource::collection(resource: $patterns);
    }

    protected function searchPattern(SearchRequest &$request): Collection
    {
        $q = $this->getBasePatternQuery();

        $this->applyFilters(
            query: $q,
            request: $request,
        );

        $q->select([
            'id',
            'title',
        ]);

        return $q->orderBy('title', 'asc')->get();
    }

    protected function getBasePatternQuery(): Builder
    {
        return Pattern::query();
    }

    protected function applyFilters(Builder &$query, SearchRequest &$request): void
    {
        $q = $request->input(key: 'q');

        $query->where('title', 'LIKE', "%{$q}%");

        $hasReviews = (bool) $request->input('has_reviews', false);

        if($hasReviews === true) {
            $query->whereHas('reviews');
        }
    }
}
