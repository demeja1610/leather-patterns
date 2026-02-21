<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReplaceMarkedPatternAuthorsForPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:replace-marked-pattern-authors-for-patterns';

    protected $description = 'Replace marked with `replace_id` in DB pattern authors for all patterns with pattern author with specified ID in `replace_id`';

    public function handle(): void
    {
        $q = Pattern::query()
            ->whereHas(relation: 'author', callback: fn(Builder $query) => $query->whereNotNull('replace_id'))
            ->with(relations: 'author.replacement');

        $count = $q->count();

        $this->info(string: "Total patterns found: {$count}");

        $result = 0;

        $q->chunkById(
            count: 250,
            callback: function (Collection $patterns) use (&$result): void {
                $pattern = $patterns->first();

                /**
                 * @var \App\Models\Pattern $pattern
                 */
                foreach ($patterns as $pattern) {

                    $this->info(string: "Replacing author with name: `{$pattern->author->name}` to author with name: {$pattern->author->replacement->name} for pattern with ID: {$pattern->id}");

                    $pattern->update([
                        'author_id' => $pattern->author->replacement->id,
                    ]);

                    $result++;
                }
            },
        );

        $this->info(string: "Pattern authors replaced successfully, {$result} pattern records were affected.");
    }
}
