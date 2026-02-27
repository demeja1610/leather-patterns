<?php

namespace App\Observers;

use App\Models\PatternCategory;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternCategoriesJob;

class PatternCategoryObserver
{
    public function updated(PatternCategory $category): void
    {
        if ($category->remove_on_appear === true) {
            dispatch(new RemoveFromPatternsMarkedForRemovalPatternCategoriesJob(categoryId: $category->id));
        }
    }
}
