<?php

namespace App\Observers;

use App\Models\PatternTag;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternTagsInPatternsJob;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternTagsJob;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternTagsToPatternAuthorInPatternsJob;

class PatternTagObserver
{
    public function updated(PatternTag $tag): void
    {
        if ($tag->remove_on_appear === true) {
            dispatch(new RemoveFromPatternsMarkedForRemovalPatternTagsJob(tagId: $tag->id));
        }

        if ($tag->replace_id !== null && $tag->isDirty('replace_id')) {
            dispatch(new ReplaceMarkedForReplacePatternTagsInPatternsJob(tagId: $tag->id));
        }

        if ($tag->replace_author_id !== null && $tag->isDirty('replace_author_id')) {
            dispatch(new ReplaceMarkedForReplacePatternTagsToPatternAuthorInPatternsJob(tagId: $tag->id));
        }
    }
}
