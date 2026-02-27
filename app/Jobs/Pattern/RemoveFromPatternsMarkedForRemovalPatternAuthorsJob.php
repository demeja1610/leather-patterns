<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveFromPatternsMarkedForRemovalPatternAuthorsJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'remove from pattern(s) pattern author(s) marked for removal';

    public function __construct(
        public ?int $patternId = null,
        public ?int $authorId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query();

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('id', $this->patternId);
        }

        $q->whereHas(relation: 'author', callback: function (Builder $sq) {
            $sq->where('remove_on_appear', true);

            if ($this->authorId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern author ID: {$this->authorId}");

                $sq->where('id', $this->authorId);
            }

            return $sq;
        });

        $result = $q->update(['author_id' => null]);

        Log::info(ucfirst($this->actionName) . ". {$result} patterns with author marked for removal was updated");

        Log::info("Finish {$this->actionName}");
    }
}
