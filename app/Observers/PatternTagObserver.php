<?php

namespace App\Observers;

use App\Models\PatternTag;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternTagsJob;

class PatternTagObserver
{
    public function updated(PatternTag $tag): void
    {
        if ($tag->remove_on_appear === true) {
            dispatch(new RemoveFromPatternsMarkedForRemovalPatternTagsJob(tagId: $tag->id));
        }
    }
}
