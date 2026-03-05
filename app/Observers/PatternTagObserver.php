<?php

namespace App\Observers;

use App\Models\PatternTag;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternTagsInPatternsJob;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternTagsJob;

class PatternTagObserver
{
    public function updated(PatternTag $tag): void
    {
        if ($tag->remove_on_appear === true) {
            dispatch(new RemoveFromPatternsMarkedForRemovalPatternTagsJob(tagId: $tag->id));
        }

        if (
            ($tag->replace_id !== null && $tag->isDirty('replace_id')) ||
            ($tag->replace_author_id !== null && $tag->isDirty('replace_author_id')) ||
            ($tag->replace_category_id !== null && $tag->isDirty('replace_category_id'))
        ) {
            dispatch(new ReplaceMarkedForReplacePatternTagsInPatternsJob(tagId: $tag->id));
        }
    }
}
