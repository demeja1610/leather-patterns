<?php

declare(strict_types=1);

namespace App\Http\Controllers\PatternTag\Api\v1;

use App\Models\PatternTag;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\PatternTag\Api\v1\GetAllRequest;
use App\Http\Resources\Api\PatternTag\PatternTagResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetAllController extends Controller
{
    public function __invoke(GetAllRequest $request): AnonymousResourceCollection
    {
        $patternTags = $this->getAllPatternTags(
            request: $request,
        );

        return PatternTagResource::collection(resource: $patternTags);
    }

    protected function getAllPatternTags(GetAllRequest &$request): Collection
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

        return $q->orderBy('id', 'asc')->get();
    }

    protected function getBasePatternTagQuery(): Builder
    {
        return PatternTag::query()
            ->where(column: 'is_published', operator: true);
    }

    protected function applyFilters(Builder &$query, GetAllRequest &$request): void
    {
        $from = $request->input(key: 'from');

        if ($from !== null) {
            $query->where(column: 'id', operator: '>', value: (int) $from);
        }
    }
}
