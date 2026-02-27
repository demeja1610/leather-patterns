<?php

namespace App\Observers;

use App\Models\PatternCategory;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternCategorisJob;

class PatternCategoryObserver
{
    public function updated(PatternCategory $category): void
    {
        if ($category->remove_on_appear === true) {
            dispatch(new RemoveFromPatternsMarkedForRemovalPatternCategorisJob(categoryId: $category->id));
        }
    }
}
