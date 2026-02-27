<?php

namespace App\Observers;

use App\Models\PatternReview;
use App\Jobs\Pattern\CalculatePatternAverageRatingJob;

class PatternReviewObserver
{
    public function updated(PatternReview $review): void
    {
        if ($review->isDirty('is_approved')) {
            CalculatePatternAverageRatingJob::dispatch($review->pattern_id);
        }
    }

    public function deleted(PatternReview $review): void
    {
        CalculatePatternAverageRatingJob::dispatch($review->pattern_id);
    }
}
