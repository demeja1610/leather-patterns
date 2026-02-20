<?php

declare(strict_types=1);

namespace App\Http\Controllers\PatternCategory\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\PatternCategory\Api\v1\GetAllRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\Api\PatternCategory\PatternCategoryResource;
use App\Models\PatternCategory;

class GetAllController extends Controller
{
    public function __invoke(GetAllRequest $request): AnonymousResourceCollection
    {
        $patternCategories = $this->getAllPatternCategories(
            request: $request,
        );

        return PatternCategoryResource::collection(resource: $patternCategories);
    }

    protected function getAllPatternCategories(GetAllRequest &$request): Collection
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

        return $q->orderBy('id', 'asc')->get();
    }

    protected function getBasePatternCategoryQuery(): Builder
    {
        return PatternCategory::query()
            ->where('is_published', true);
    }

    protected function applyFilters(Builder &$query, GetAllRequest &$request): void
    {
        $from = $request->input(key: 'from');

        if ($from !== null) {
            $query->where('id', '>', value: (int) $from);
        }
    }
}
