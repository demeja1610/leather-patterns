<?php

namespace App\Http\Controllers;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Http\Request;
use App\Models\PatternAuthor;
use App\Enum\PatternOrderEnum;
use App\Models\PatternCategory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\CursorPaginator;

class PatternsListController extends Controller
{
    public function __invoke(Request $request)
    {
        $order = $request->get('order', null);

        if (!empty($order)) {
            $order = PatternOrderEnum::tryFrom($order);
        }

        $cursor = $request->get('cursor', null);
        $search = $request->get('s', null);
        $hasVideo = $request->has('has_video');
        $hasReview = $request->has('has_review');
        $activeCategoriesIds = $request->get('category', []);
        $activeTagsIds = $request->get('tag', []);
        $activeAuthorsIds = $request->get('author', []);

        $patterns = $this->getPatterns(
            cursor: $cursor,
            search: $search,
            activeCategoriesIds: $activeCategoriesIds,
            activeTagsIds: $activeTagsIds,
            activeAuthorsIds: $activeAuthorsIds,
            hasVideo: $hasVideo,
            hasReview: $hasReview,
            order: $order
        )->appends($request->query());

        $categories = $this->getCategories(
            activeCategoriesIds: $activeCategoriesIds
        );

        $tags = $this->getTags(
            activeTagsIds: $activeTagsIds
        );

        $authors = $this->getAuthors(
            activeAuthorsIds: $activeAuthorsIds
        );

        return view('pages.pattern.list', [
            'patterns' => $patterns,
            'categories' => $categories,
            'activeCategoriesIds' => $activeCategoriesIds,
            'tags' => $tags,
            'activeTagsIds' => $activeTagsIds,
            'authors' => $authors,
            'activeAuthorsIds' => $activeAuthorsIds,
            'hasVideo' => $hasVideo,
            'hasReview' => $hasReview,
            'search' => $search,
            'order' => $order,
            'patternOrders' => PatternOrderEnum::cases(),
        ]);
    }

    protected function getPatterns(
        ?string $cursor = null,
        ?string $search = null,
        ?array $activeCategoriesIds = null,
        ?array $activeTagsIds = null,
        ?array $activeAuthorsIds = null,
        ?bool $hasVideo = null,
        ?bool $hasReview = null,
        ?PatternOrderEnum $order = null
    ): CursorPaginator {
        $q = Pattern::query();

        if (!in_array($search, [null, '', '0'], true)) {
            $q->where('title', 'like', "%$search%");
        }

        if ($activeCategoriesIds !== []) {
            $q->whereHas('categories', fn($query) => $query->whereIn('pattern_categories.id', $activeCategoriesIds));
        }

        if ($activeTagsIds !== []) {
            $q->whereHas('tags', fn($query) => $query->whereIn('pattern_tags.id', $activeTagsIds));
        }

        if ($activeAuthorsIds !== []) {
            $q->whereIn('author_id', $activeAuthorsIds);
        }

        if ($hasVideo) {
            $q->whereHas('videos');
        }

        if ($hasReview) {
            $q->whereHas('reviews');
        }

        if ($order instanceof PatternOrderEnum) {
            switch ($order) {
                case PatternOrderEnum::DATE_ASC:
                    $q->orderBy(
                        'id',
                        'asc'
                    );
                    break;
                case PatternOrderEnum::DATE_DESC:
                    $q->orderBy(
                        'id',
                        'desc'
                    );
                    break;
                case PatternOrderEnum::RATING_DESC:
                    $q->orderBy(
                        'avg_rating',
                        'desc'
                    );
                    break;
            }
        } else {
            $q->orderBy(
                'patterns.id',
                'desc'
            );
        }

        $q->with([
            'categories',
            'tags',
            'author',
            'images',
            'files',
            'meta',
        ]);

        $q->whereHas('meta', fn($query) => $query->where('pattern_downloaded', true));

        return $q->cursorPaginate(
            perPage: 16,
            cursor: $cursor
        );
    }

    protected function getCategories(array $activeCategoriesIds = []): Collection
    {
        return PatternCategory::query()
            ->get()
            ->sortBy(
                descending: true,
                callback: fn($category) => in_array($category->id, $activeCategoriesIds)
            );
    }

    protected function getTags(array $activeTagsIds = []): Collection
    {
        return PatternTag::query()
            ->get()
            ->sortBy(
                descending: true,
                callback: fn($tag) => in_array($tag->id, $activeTagsIds)
            );
    }

    protected function getAuthors(array $activeAuthorsIds = []): Collection
    {
        return PatternAuthor::query()
            ->get()
            ->sortBy(
                descending: true,
                callback: fn($author) => in_array($author->id, $activeAuthorsIds)
            );
    }
}
