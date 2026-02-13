<?php

namespace App\Http\Controllers\Pattern\Web\v1;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Http\Request;
use App\Models\PatternAuthor;
use App\Enum\PatternOrderEnum;
use App\Models\PatternCategory;
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

    public function __invoke(Request $request)
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

        return view('pages.pattern.list', [
            'categories' => $patternCategories,
            'tags' => $patternTags,
            'authors' => $patternAuthors,
            'patterns' => $patterns,
            'patternOrders' => PatternOrderEnum::cases(),
        ]);
    }

    protected function getPatternategoriesForFilter(Request &$request): Collection
    {
        $showAllPatternCategories = $request->get('show_all_pattern_categories', false);

        if ($showAllPatternCategories === false) {
            $patternCategories = $this->getSelectedPatternCategories(
                request: $request
            );

            $patternCategoriesCount = $patternCategories->count();

            if ($patternCategoriesCount < $this->patternCategoriesLimit) {
                $patternCategoriesLimit = $this->patternCategoriesLimit - $patternCategoriesCount;

                $extraCategories = Cache::remember(
                    key: "patterns_page:filter_category:first_{$patternCategoriesLimit}_categories",
                    ttl: $this->cacheTtl,
                    callback: fn() => $this->getPatternCategories(
                        limit: $patternCategoriesLimit,
                    ),
                );

                $patternCategories = $patternCategories->merge($extraCategories);
            }
        } else {
            $patternCategories = Cache::remember(
                key: 'all_pattern_categories',
                ttl: $this->cacheTtl,
                callback: fn() => $this->getPatternCategories()
            );
        }

        return $patternCategories;
    }

    protected function getPatternTagsForFilter(Request &$request): Collection
    {
        $showAllPatternTags = $request->get('show_all_pattern_tags', false);

        if ($showAllPatternTags === false) {
            $patternTags = $this->getSelectedPatternTags(
                request: $request
            );

            $patternTagsCount = $patternTags->count();

            if ($patternTagsCount < $this->patternTagsLimit) {
                $patternTagsLimit = $this->patternTagsLimit - $patternTagsCount;

                $extraTags = Cache::remember(
                    key: "patterns_page:filter_tag:first_{$patternTagsLimit}_tags",
                    ttl: $this->cacheTtl,
                    callback: fn() =>  $this->getPatternTags(
                        limit: $patternTagsLimit,
                    ),
                );

                $patternTags = $patternTags->merge($extraTags);
            }
        } else {
            $patternTags = Cache::remember(
                key: 'all_pattern_tags',
                ttl: $this->cacheTtl,
                callback: fn() => $this->getPatternTags()
            );
        }

        return $patternTags;
    }

    protected function getPatternAuthorsForFilter(Request &$request): Collection
    {
        $showAllPatternAuthors = $request->get('show_all_pattern_authors', false);

        if ($showAllPatternAuthors === false) {
            $patternAuthors = $this->getSelectedPatternAuthors(
                request: $request
            );

            $patternAuthorsCount = $patternAuthors->count();

            if ($patternAuthorsCount < $this->patternAuthorsLimit) {
                $patternAuthorsLimit = $this->patternAuthorsLimit - $patternAuthorsCount;

                $extraAuthors = Cache::remember(
                    key: "patterns_page:filter_author:first_{$patternAuthorsLimit}_authors",
                    ttl: $this->cacheTtl,
                    callback: fn() => $this->getPatternAuthors(
                        limit: $patternAuthorsLimit,
                    ),
                );

                $patternAuthors = $patternAuthors->merge($extraAuthors);
            }
        } else {
            $patternAuthors = Cache::remember(
                key: 'all_pattern_authors',
                ttl: $this->cacheTtl,
                callback: fn() => $this->getPatternAuthors()
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

        $q->with([
            'categories' => function (BelongsToMany $sq) {
                $table = $sq->getRelated()->getTable();

                $sq->select([
                    "{$table}.id",
                    "{$table}.name"
                ]);
            },
            'tags' => function (BelongsToMany $sq) {
                $table = $sq->getRelated()->getTable();

                $sq->select([
                    "{$table}.id",
                    "{$table}.name"
                ]);
            },
            'author' => function (BelongsTo $sq) {
                $table = $sq->getRelated()->getTable();

                $sq->select([
                    "{$table}.id",
                    "{$table}.name"
                ]);
            },
            'images' => function (HasMany $sq) {
                $table = $sq->getRelated()->getTable();

                $sq->select([
                    "{$table}.path",
                    "{$table}.pattern_id",
                ]);
            },
            'files' => function (HasMany $sq) {
                $table = $sq->getRelated()->getTable();

                $sq->select([
                    "{$table}.path",
                    "{$table}.extension",
                    "{$table}.type",
                    "{$table}.pattern_id",
                ]);
            },
        ]);

        $q->whereHas('meta', fn($query) => $query->select('pattern_downloaded')->where('pattern_downloaded', true));

        $cursor = $request->get('cursor', null);

        return $q->cursorPaginate(
            perPage: 16,
            cursor: $cursor
        );
    }

    protected function getBasePatternCategoryQuery(): Builder
    {
        return PatternCategory::query();
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
            'name'
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRequiredPatternTagColumns(): array
    {
        return [
            'id',
            'name'
        ];
    }

    /**
     * @return array<string>
     */
    protected function getRequiredPatternAuthorColumns(): array
    {
        return [
            'id',
            'name'
        ];
    }

    protected function getSelectedPatternCategories(Request &$request): Collection
    {
        $ids = $request->get('category', []);
        $idsCount = count($ids);

        if ($idsCount === 0) {
            return new Collection;
        }

        $q = $this->getBasePatternCategoryQuery();

        if ($ids !== []) {
            if ($idsCount === 1) {
                $q->where('id', reset($ids));
            } else {
                $q->whereIn('id', $ids);
            }
        }

        return $q->select($this->getRequiredPatternCategoryColumns())->get();
    }

    protected function getSelectedPatternTags(Request &$request): Collection
    {
        $ids = $request->get('tag', []);
        $idsCount = count($ids);

        if ($idsCount === 0) {
            return new Collection;
        }

        $q = $this->getBasePatternTagQuery();

        if ($ids !== []) {
            if ($idsCount === 1) {
                $q->where('id', reset($ids));
            } else {
                $q->whereIn('id', $ids);
            }
        }

        return $q->select($this->getRequiredPatternTagColumns())->get();
    }

    protected function getSelectedPatternAuthors(Request &$request): Collection
    {
        $ids = $request->get('author', []);
        $idsCount = count($ids);

        if ($idsCount === 0) {
            return new Collection;
        }

        $q = $this->getBasePatternAuthorQuery();

        if ($ids !== []) {
            if ($idsCount === 1) {
                $q->where('id', reset($ids));
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
            $q->limit($limit);
        }

        return $q->select($this->getRequiredPatternCategoryColumns())->get();
    }

    protected function getPatternTags(?int $limit = null): Collection
    {
        $q = $this->getBasePatternTagQuery()
            ->orderBy('id');

        if ($limit !== null) {
            $q->limit($limit);
        }

        return $q->select($this->getRequiredPatternTagColumns())->get();
    }

    protected function getPatternAuthors(?int $limit = null): Collection
    {
        $q = $this->getBasePatternAuthorQuery()
            ->orderBy('id');

        if ($limit !== null) {
            $q->limit($limit);
        }

        return $q->select($this->getRequiredPatternAuthorColumns())->get();
    }

    protected function applyFilters(Builder &$query, Request &$request): void
    {
        $search = $request->get('s', null);

        if ($search !== null && $search !== '') {
            $query->where('title', 'like', "%$search%");
        }

        $activeCategoriesIds = $request->get('category', []);

        if ($activeCategoriesIds !== []) {
            $query->whereHas('categories', fn($query) => $query->whereIn('pattern_categories.id', $activeCategoriesIds));
        }

        $activeTagsIds = $request->get('tag', []);

        if ($activeTagsIds !== []) {
            $query->whereHas('tags', fn($query) => $query->whereIn('pattern_tags.id', $activeTagsIds));
        }

        $hasAuthor = $request->has('has_author');
        $activeAuthorsIds = $request->get('author', []);

        if ($hasAuthor === true && $activeAuthorsIds === []) {
            $query->whereNotNull('author_id');
        } else if ($hasAuthor === true && $activeAuthorsIds !== []) {
            $query->whereIn('author_id', $activeAuthorsIds);
        } else if ($hasAuthor === false && $activeAuthorsIds !== []) {
            $query->whereIn('author_id', $activeAuthorsIds);
        }

        $hasVideo = $request->has('has_video');

        if ($hasVideo === true) {
            $query->whereHas('videos');
        }

        $hasReview = $request->has('has_review');

        if ($hasReview === true) {
            $query->whereHas('reviews');
        }

        $orderStr = $request->get('order', null);

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
