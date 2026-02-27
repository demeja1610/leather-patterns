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

        $exceptId = $request->input('except_id');

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $patternReplaceable = $request->input('pattern_replaceable');

        if ($patternReplaceable !== null) {
            $patternReplaceable = (bool) $patternReplaceable;

            if ($patternReplaceable === true) {
                $query->whereNotNull('replace_id')
                    ->whereNotNull('replace_author_id')
                    ->whereNotNull('replace_category_id');
            } else {
                $query->whereNull('replace_id')
                    ->whereNull('replace_author_id')
                    ->whereNull('replace_category_id');
            }
        }

        $patternRemovable = $request->input('pattern_removable');

        if ($patternRemovable !== null) {
            $query->where('remove_on_appear', (bool) $patternRemovable);
        }
    }
}
