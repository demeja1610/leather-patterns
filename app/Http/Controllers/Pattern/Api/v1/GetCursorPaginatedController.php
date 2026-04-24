<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pattern\Api\v1;

use App\Models\Pattern;
use App\Enum\PatternOrderEnum;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Http\Requests\Pattern\Api\v1\GetCursorPaginatedRequest;
use App\Http\Resources\Api\v1\App\Pattern\PatternResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetCursorPaginatedController extends Controller
{
    public function __invoke(GetCursorPaginatedRequest $request): AnonymousResourceCollection
    {
        $patterns = $this->getPaginatedPatterns(
            request: $request,
        )->appends($request->query());

        return PatternResource::collection($patterns);
    }

    protected function getPaginatedPatterns(GetCursorPaginatedRequest &$request): CursorPaginator
    {
        $q = Pattern::query();

        $this->applyFilters(
            query: $q,
            request: $request,
        );

        $q->whereHas('files', fn(Builder $sq) => $sq->whereNull('parent_id'))
            ->where('is_published', true);

        $q->with(
            relations: [
                'categories' => function (BelongsToMany $sq): void {
                    $table = $sq->getRelated()->getTable();

                    $sq->where('is_published', true);

                    $sq->select([
                        "{$table}.id",
                        "{$table}.name",
                    ]);
                },
                'tags' => function (BelongsToMany $sq): void {
                    $table = $sq->getRelated()->getTable();

                    $sq->where('is_published', true);

                    $sq->select([
                        "{$table}.id",
                        "{$table}.name",
                    ]);
                },
                'author' => function (BelongsTo $sq): void {
                    $table = $sq->getRelated()->getTable();

                    $sq->where('is_published', true);

                    $sq->select([
                        "{$table}.id",
                        "{$table}.name",
                    ]);
                },
                'images' => function (HasMany $sq): void {
                    $table = $sq->getRelated()->getTable();

                    $sq->select([
                        "{$table}.path",
                        "{$table}.pattern_id",
                    ]);
                },
                'files' => function (HasMany $sq): void {
                    $table = $sq->getRelated()->getTable();

                    $sq->whereNull('parent_id')
                        ->select([
                            "{$table}.path",
                            "{$table}.extension",
                            "{$table}.type",
                            "{$table}.pattern_id",
                        ]);
                },
            ],
        );

        $q->withCount('reviews');

        $cursor = $request->input(key: 'cursor');

        return $q->cursorPaginate(
            perPage: 16,
            cursor: $cursor,
        );
    }

    protected function applyFilters(Builder &$query, GetCursorPaginatedRequest &$request): void
    {
        $search = $request->input(key: 's');

        if ($search !== null && $search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $category = $request->input(key: 'category', default: []);

        if ($category !== []) {
            $query->whereHas(
                relation: 'categories',
                callback: fn($query) => $query
                    ->whereIn('pattern_categories.id', $category)
                    ->where('is_published', true)
            );
        }

        $tag = $request->input(key: 'tag', default: []);

        if ($tag !== []) {
            $query->whereHas(
                relation: 'tags',
                callback: fn($query) => $query
                    ->whereIn('pattern_tags.id', $tag)
                    ->where('is_published', true)
            );
        }

        $hasAuthor = $request->has(key: 'has_author');

        $author = $request->input(key: 'author', default: []);

        if ($hasAuthor === true && $author === []) {
            $query->whereNotNull('author_id');
        } elseif ($hasAuthor === true && $author !== []) {
            $query->whereIn('author_id', $author);
        } elseif ($hasAuthor === false && $author !== []) {
            $query->whereIn('author_id', $author);
        }

        $hasVideo = $request->has(key: 'has_video');

        if ($hasVideo === true) {
            $query->whereHas(relation: 'videos');
        }

        $hasReview = $request->has(key: 'has_review');

        if ($hasReview === true) {
            $query->whereHas(relation: 'reviews', callback: fn(Builder $sq) => $sq->where('is_approved', true));
        }

        $orderStr = $request->input(key: 'order');

        if ($orderStr !== null) {
            $order = PatternOrderEnum::tryFrom($orderStr);
        }

        if (!empty($order)) {
            switch ($order) {
                case PatternOrderEnum::DATE_ASC:
                    $query->orderBy('id');

                    break;
                case PatternOrderEnum::DATE_DESC:
                    $query->orderByDesc('id');

                    break;
                case PatternOrderEnum::RATING_DESC:
                    $query->orderByDesc('avg_rating');

                    break;
            }
        } else {
            $query->orderByDesc(
                'patterns.id',
            );
        }
    }
}
