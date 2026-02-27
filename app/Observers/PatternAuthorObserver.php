<?php

namespace App\Observers;

use App\Jobs\Pattern\RemoveFromPatternsMarkedForRemovalPatternAuthorsJob;
use App\Models\PatternAuthor;

class PatternAuthorObserver
{
    public function updated(PatternAuthor $author): void
    {
        if ($author->remove_on_appear === true) {
            dispatch(new RemoveFromPatternsMarkedForRemovalPatternAuthorsJob(authorId: $author->id));
        }
    }
}
