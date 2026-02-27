<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedForReplacePatternAuthorsInPatternsJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'replace marked for replace pattern author(s) in pattern(s)';

    public function __construct(
        public ?int $patternId = null,
        public ?int $authorId = null,
    ) {}

    public function handle(): void
    {
        Log::info("Start {$this->actionName}");

        $q = Pattern::query();

        $q->whereHas(relation: 'author', callback: function (Builder $sq) {
            $sq->whereNotNull('replace_id');

            if ($this->authorId !== null) {
                Log::info(ucfirst($this->actionName) . ". Pattern author ID: {$this->authorId}");

                $sq->where('id', $this->authorId);
            }

            return $sq;
        });

        $q->with(relations: 'author.replacement');

        $result = 0;

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('id', $this->patternId);

            $pattern = $q->first();

            if ($pattern === null) {
                Log::info(ucfirst($this->actionName) . ". Specified pattern with ID: {$this->patternId} don't have author to replace or don't exists");

                return;
            }

            $this->replaceMarkedAuthorInPattern(pattern: $pattern);

            $result++;
        } else {
            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$result): void {
                    /**
                     * @var \App\Models\Pattern $pattern
                     */
                    foreach ($patterns as $pattern) {
                        $this->replaceMarkedAuthorInPattern(pattern: $pattern);

                        $result++;
                    }
                },
            );
        }

        Log::info("Finish {$this->actionName}, {$result} patterns was updated");
    }

    protected function replaceMarkedAuthorInPattern(Pattern $pattern): void
    {
        $pId = $pattern->id; // pattern ID
        $pAId = $pattern->author->id; // pattern author ID
        $pARId = $pattern->author->replacement->id; // pattern author replacement ID

        Log::info(
            ucfirst($this->actionName) . ". Replacing author with ID: {$pAId} to author with ID: {$pARId} for pattern with id {$pId}"
        );

        $pattern->update([
            'author_id' => $pARId,
        ]);
    }
}
