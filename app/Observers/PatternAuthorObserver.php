<?php

namespace App\Observers;

use App\Models\PatternAuthor;
use App\Jobs\Pattern\ReplaceMarkedForReplacePatternAuthorsInPatternsJob;
use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternAuthorsJob;

class PatternAuthorObserver
{
    public function updated(PatternAuthor $author): void
    {
        if ($author->remove_on_appear === true) {
            dispatch(new RemoveFromPatternsMarkedForRemovalPatternAuthorsJob(authorId: $author->id));
        }

        if ($author->replace_id !== null && $author->isDirty('replace_id')) {
            dispatch(new ReplaceMarkedForReplacePatternAuthorsInPatternsJob(authorId: $author->id));
        }
    }
}
