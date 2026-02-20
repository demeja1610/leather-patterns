<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pattern\Web\v1;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Http\Request;
use App\Models\PatternAuthor;
use App\Enum\PatternOrderEnum;
use App\Models\PatternCategory;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ListController extends Controller
{
    protected int $patternCategoriesLimit = 10;

    protected int $patternTagsLimit = 10;

    protected int $patternAuthorsLimit = 10;

    protected int $cacheTtl = 300;

    public function __invoke(Request $request): View
    {
        $patternCategories = $this->getPatternategoriesForFilter(
            request: $request,
        );

        $patternTags = $this->getPatternTagsForFilter(
            request: $request,
        );

        $patternAuthors = $this->getPatternAuthorsForFilter(
            request: $request,
        );

        $patterns = $this->getPaginatedPatterns(
            request: $request,
        )->appends($request->query());

        return view(view: 'pages.pattern.list', data: [
            'categories' => $patternCategories,
            'categoriesLimit' => $this->patternCategoriesLimit,
            'tags' => $patternTags,
            'authors' => $patternAuthors,
            'patterns' => $patterns,
            'patternOrders' => PatternOrderEnum::cases(),
        ]);
    }

    protected function getPatternategoriesForFilter(Request &$request): Collection
    {
        $showAllPatternCategories = $request->get(key: 'show_all_pattern_categories', default: false);

        if ($showAllPatternCategories === false) {
            $patternCategories = $this->getSelectedPatternCategories(
                request: $request,
            );

            $patternCategoriesCount = $patternCategories->count();

            if ($patternCategoriesCount < $this->patternCategoriesLimit) {
                $patternCategoriesLimit = $this->patternCategoriesLimit - $patternCategoriesCount;

                $extraCategories = Cache::remember(
                    key: "patterns_page:filter_category:first_{$patternCategoriesLimit}_categories",
                    ttl: $this->cacheTtl,
                    callback: fn(): Collection => $this->getPatternCategories(
                        limit: $patternCategoriesLimit,
                    ),
                );

                $patternCategories = $patternCategories->merge(items: $extraCategories);
            }
        } else {
            $patternCategories = Cache::remember(
                key: 'all_pattern_categories',
                ttl: $this->cacheTtl,
                callback: fn(): Collection => $this->getPatternCategories(),
            );
        }

        return $patternCategories;
    }

    protected function getPatternTagsForFilter(Request &$request): Collection
    {
        $showAllPatternTags = $request->get(key: 'show_all_pattern_tags', default: false);

        if ($showAllPatternTags === false) {
            $patternTags = $this->getSelectedPatternTags(
                request: $request,
            );

            $patternTagsCount = $patternTags->count();

            if ($patternTagsCount < $this->patternTagsLimit) {
                $patternTagsLimit = $this->patternTagsLimit - $patternTagsCount;

                $extraTags = Cache::remember(
                    key: "patterns_page:filter_tag:first_{$patternTagsLimit}_tags",
                    ttl: $this->cacheTtl,
                    callback: fn(): Collection =>  $this->getPatternTags(
                        limit: $patternTagsLimit,
                    ),
                );

                $patternTags = $patternTags->merge(items: $extraTags);
            }
        } else {
            $patternTags = Cache::remember(
                key: 'all_pattern_tags',
                ttl: $this->cacheTtl,
                callback: fn(): Collection => $this->getPatternTags(),
            );
        }

        return $patternTags;
    }

    protected function getPatternAuthorsForFilter(Request &$request): Collection
    {
        $showAllPatternAuthors = $request->get(key: 'show_all_pattern_authors', default: false);

        if ($showAllPatternAuthors === false) {
            $patternAuthors = $this->getSelectedPatternAuthors(
                request: $request,
            );

            $patternAuthorsCount = $patternAuthors->count();

            if ($patternAuthorsCount < $this->patternAuthorsLimit) {
                $patternAuthorsLimit = $this->patternAuthorsLimit - $patternAuthorsCount;

                $extraAuthors = Cache::remember(
                    key: "patterns_page:filter_author:first_{$patternAuthorsLimit}_authors",
                    ttl: $this->cacheTtl,
                    callback: fn(): Collection => $this->getPatternAuthors(
                        limit: $patternAuthorsLimit,
                    ),
                );

                $patternAuthors = $patternAuthors->merge(items: $extraAuthors);
            }
        } else {
            $patternAuthors = Cache::remember(
                key: 'all_pattern_authors',
                ttl: $this->cacheTtl,
                callback: fn(): Collection => $this->getPatternAuthors(),
            );
        }

        return $patternAuthors;
    }

    protected function getPaginatedPatterns(Request &$request): CursorPaginator
    {
        $q = Pattern::query();

        $this->applyFilters(
            query: $q,
            request: $request,
        );

        $q->with(relations: [
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

                $sq->select([
                    "{$table}.id",
                    "{$table}.name",
                ]);
            },
            'author' => function (BelongsTo $sq): void {
                $table = $sq->getRelated()->getTable();

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

                $sq->select([
                    "{$table}.path",
                    "{$table}.extension",
                    "{$table}.type",
                    "{$table}.pattern_id",
                ]);
            },
        ]);

        $q->whereHas(relation: 'meta', callback: fn($query) => $query->select('pattern_downloaded')->where(column: 'pattern_downloaded', operator: true));

        $cursor = $request->get(key: 'cursor');

        return $q->cursorPaginate(
            perPage: 16,
            cursor: $cursor,
        );
    }

    protected function getBasePatternCategoryQuery(): Builder
    {
        return PatternCategory::query()
            ->where(column: 'is_published', operator: true);
    }

    protected function getBasePatternTagQuery(): Builder
    {
        return PatternTag::query();
    }

    protected function getBasePatternAuthorQuery(): Builder
    {
        return PatternAuthor::query();
    }

    /**
     * @return array<string>
     */
    protected function getRequiredPatternCategoryColumns(): array
    {
        return [
            'id',
            'name',
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRequiredPatternTagColumns(): array
    {
        return [
            'id',
            'name',
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRequiredPatternAuthorColumns(): array
    {
        return [
            'id',
            'name',
        ];
    }

    protected function getSelectedPatternCategories(Request &$request): Collection
    {
        $ids = $request->get(key: 'category', default: []);
        $idsCount = count(value: $ids);

        if ($idsCount === 0) {
            return new Collection();
        }

        $q = $this->getBasePatternCategoryQuery();

        if ($ids !== []) {
            if ($idsCount === 1) {
                $q->where(column: 'id', operator: reset(array: $ids));
            } else {
                $q->whereIn('id', $ids);
            }
        }

        return $q->select($this->getRequiredPatternCategoryColumns())->get();
    }

    protected function getSelectedPatternTags(Request &$request): Collection
    {
        $ids = $request->get(key: 'tag', default: []);
        $idsCount = count(value: $ids);

        if ($idsCount === 0) {
            return new Collection();
        }

        $q = $this->getBasePatternTagQuery();

        if ($ids !== []) {
            if ($idsCount === 1) {
                $q->where(column: 'id', operator: reset(array: $ids));
            } else {
                $q->whereIn('id', $ids);
            }
        }

        return $q->select($this->getRequiredPatternTagColumns())->get();
    }

    protected function getSelectedPatternAuthors(Request &$request): Collection
    {
        $ids = $request->get(key: 'author', default: []);
        $idsCount = count(value: $ids);

        if ($idsCount === 0) {
            return new Collection();
        }

        $q = $this->getBasePatternAuthorQuery();

        if ($ids !== []) {
            if ($idsCount === 1) {
                $q->where(column: 'id', operator: reset(array: $ids));
            } else {
                $q->whereIn('id', $ids);
            }
        }

        return $q->select($this->getRequiredPatternAuthorColumns())->get();
    }

    protected function getPatternCategories(?int $limit = null): Collection
    {
        $q = $this->getBasePatternCategoryQuery()
            ->orderBy('id');

        if ($limit !== null) {
            $q->limit(value: $limit);
        }

        return $q->select(columns: $this->getRequiredPatternCategoryColumns())->get();
    }

    protected function getPatternTags(?int $limit = null): Collection
    {
        $q = $this->getBasePatternTagQuery()
            ->orderBy('id');

        if ($limit !== null) {
            $q->limit(value: $limit);
        }

        return $q->select(columns: $this->getRequiredPatternTagColumns())->get();
    }

    protected function getPatternAuthors(?int $limit = null): Collection
    {
        $q = $this->getBasePatternAuthorQuery()
            ->orderBy('id');

        if ($limit !== null) {
            $q->limit(value: $limit);
        }

        return $q->select(columns: $this->getRequiredPatternAuthorColumns())->get();
    }

    protected function applyFilters(Builder &$query, Request &$request): void
    {
        $search = $request->get(key: 's');

        if ($search !== null && $search !== '') {
            $query->where(column: 'title', operator: 'like', value: "%{$search}%");
        }

        $activeCategoriesIds = $request->get(key: 'category', default: []);

        if ($activeCategoriesIds !== []) {
            $query->whereHas(relation: 'categories', callback: fn($query) => $query->whereIn('pattern_categories.id', $activeCategoriesIds));
        }

        $activeTagsIds = $request->get(key: 'tag', default: []);

        if ($activeTagsIds !== []) {
            $query->whereHas(relation: 'tags', callback: fn($query) => $query->whereIn('pattern_tags.id', $activeTagsIds));
        }

        $hasAuthor = $request->has(key: 'has_author');
        $activeAuthorsIds = $request->get(key: 'author', default: []);

        if ($hasAuthor === true && $activeAuthorsIds === []) {
            $query->whereNotNull('author_id');
        } elseif ($hasAuthor === true && $activeAuthorsIds !== []) {
            $query->whereIn('author_id', $activeAuthorsIds);
        } elseif ($hasAuthor === false && $activeAuthorsIds !== []) {
            $query->whereIn('author_id', $activeAuthorsIds);
        }

        $hasVideo = $request->has(key: 'has_video');

        if ($hasVideo === true) {
            $query->whereHas(relation: 'videos');
        }

        $hasReview = $request->has(key: 'has_review');

        if ($hasReview === true) {
            $query->whereHas(relation: 'reviews');
        }

        $orderStr = $request->get(key: 'order');

        if ($orderStr !== null) {
            $order = PatternOrderEnum::tryFrom(value: $orderStr);
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
