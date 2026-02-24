<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternAuthor\Api\v1;

use App\Models\PatternAuthor;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\Admin\PatternAuthor\Api\v1\SearchRequest;
use App\Http\Resources\Api\PatternAuthor\PatternAuthorResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): AnonymousResourceCollection
    {
        $patternAuthors = $this->searchPatternAuthors(
            request: $request,
        );

        return PatternAuthorResource::collection(resource: $patternAuthors);
    }

    protected function searchPatternAuthors(SearchRequest &$request): Collection
    {
        $q = $this->getBasePatternAuthorQuery();

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

    protected function getBasePatternAuthorQuery(): Builder
    {
        return PatternAuthor::query();
    }

    protected function applyFilters(Builder &$query, SearchRequest &$request): void
    {
        $q = $request->input(key: 'q');

        $query->where('name', 'LIKE', "%{$q}%");
    }
}
