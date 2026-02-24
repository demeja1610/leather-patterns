<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternReview\Page;

use Carbon\Carbon;
use App\Models\PatternReview;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\PatternReview\ListRequest;

class ListPageController extends Controller
{
    protected array $activeFilters = [];

    public function __invoke(ListRequest $request): View
    {
        $reviews = $this->getReviews(request: $request);

        return view(view: 'pages.admin.pattern-review.list', data: [
            'activeFilters' => $this->activeFilters,
            'reviews' => $reviews,
        ]);
    }

    protected function getReviews(ListRequest &$request)
    {
        $cursor = $request->input(key: 'cursor');

        $q = PatternReview::query();

        $this->applyFilters(
            request: $request,
            query: $q,
        );

        $q->with(relations: 'pattern');

        return $q->orderBy('id', 'desc')->cursorPaginate(
            perPage: 30,
            cursor: $cursor,
        )->withQueryString();
    }

    protected function applyFilters(ListRequest &$request, Builder &$query): void
    {
        $id = $request->input(key: 'id');

        if ($id !== null) {
            $this->activeFilters['id'] = $id;

            $query->where('id', $id);
        }

        $reviewerName = $request->input(key: 'reviewer_name');

        if ($reviewerName !== null) {
            $this->activeFilters['reviewer_name'] = $reviewerName;

            $query->where('reviewer_name', 'LIKE', "%{$reviewerName}%");
        }

        $ratingMoreThan = $request->input(key: 'rating_more_than');

        if ($ratingMoreThan !== null) {
            $this->activeFilters['rating_more_than'] = (int) $ratingMoreThan;

            $query->where('rating', '>=', $ratingMoreThan);
        }

        $ratingLessThan = $request->input(key: 'rating_less_than');

        if ($ratingLessThan !== null) {
            $this->activeFilters['rating_less_than'] = (int) $ratingLessThan;

            $query->where('rating', '<=', $ratingLessThan);
        }

        $olderThanStr = $request->input(key: 'older_than');

        if ($olderThanStr !== null) {
            $olderThan = Carbon::parse(time: $olderThanStr);

            $this->activeFilters['older_than'] = $olderThan;

            $query->where('created_at', '<', $olderThan);
        }

        $newerThanStr = $request->input(key: 'newer_than');

        if ($newerThanStr !== null) {
            $newerThan = Carbon::parse(time: $newerThanStr);

            $this->activeFilters['newer_than'] = $newerThan;

            $query->where('created_at', '>', $newerThan);
        }

        $isPublished = $request->input(key: 'is_approved');

        if ($isPublished !== null) {
            $this->activeFilters['is_approved'] = (bool) $isPublished;

            if ((bool) $isPublished) {
                $query->where('is_approved', true);
            } else {
                $query->where('is_approved', false);
            }
        }

        $hasRating = $request->input(key: 'has_rating');

        if ($hasRating !== null) {
            $this->activeFilters['has_rating'] = (bool) $hasRating;

            if ((bool) $hasRating) {
                $query->where('rating', '>', 0);
            } else {
                $query->where('rating', '=', 0);
            }
        }

        $hasUser = $request->input(key: 'has_user');

        if ($hasUser !== null) {
            $this->activeFilters['has_user'] = (bool) $hasUser;

            if ((bool) $hasUser) {
                $query->whereNotNull('user_id');
            } else {
                $query->whereNull('user_id');
            }
        }
    }
}
