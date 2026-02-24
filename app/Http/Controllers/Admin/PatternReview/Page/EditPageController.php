<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternReview\Page;

use App\Models\PatternReview;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $review = $this->getReview(id: $id);

        if (!$review instanceof PatternReview) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }

        $this->loadRelations(review: $review);

        return view(view: 'pages.admin.pattern-review.edit', data: [
            'review' => $review,
        ]);
    }

    protected function getReview($id): ?PatternReview
    {
        return PatternReview::query()->find(id: $id);
    }

    protected function loadRelations(PatternReview &$review): void
    {
        $relations = [
            'pattern',
        ];

        if ($review->user_id !== null) {
            $relations[]  = 'user';
        }

        $review->load(relations: $relations);
    }
}
