<?php

declare(strict_types=1);

namespace App\Http\Controllers\PatternAuthor\Api\v1;

use App\Models\PatternAuthor;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\PatternAuthor\Api\v1\GetAllRequest;
use App\Http\Resources\Api\PatternAuthor\PatternAuthorResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetAllController extends Controller
{
    public function __invoke(GetAllRequest $request): AnonymousResourceCollection
    {
        $patternAuthors = $this->getAllPatternAuthors(
            request: $request,
        );

        return PatternAuthorResource::collection(resource: $patternAuthors);
    }

    protected function getAllPatternAuthors(GetAllRequest &$request): Collection
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
        return PatternAuthor::query()
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
