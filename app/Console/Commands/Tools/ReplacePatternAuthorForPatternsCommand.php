<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Models\PatternAuthor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ReplacePatternAuthorForPatternsCommand extends Command
{
    protected $signature = 'tools:replace-pattern-author-for-patterns {--from=} {--to=} {--id=}';

    protected $description = 'Replace specified pattern author for another specified pattern author for all patterns with that author or for a specific pattern';

    public function handle(): void
    {
        $from = $this->option(key: 'from');
        $to = $this->option(key: 'to');
        $id = $this->option(key: 'id');

        if (!$from || !$to) {
            $this->error(string: 'Both --from and --to options are required.');

            return;
        }

        $from = mb_strtolower(string: $from);
        $to = mb_strtolower(string: $to);

        $fromAuthor = PatternAuthor::query()->where(column: 'name', operator: $from)->first();
        $toAuthor = PatternAuthor::query()->where(column: 'name', operator: $to)->first();

        if (!$fromAuthor || !$toAuthor) {
            $this->error(string: 'Both from and to authors must exist.');

            return;
        }

        $this->info(string: "From author ID: {$fromAuthor->id}");
        $this->info(string: "To author ID: {$toAuthor->id}");

        $q = Pattern::query()
            ->whereHas(relation: 'author', callback: fn($query) => $query->where(column: 'name', operator: $from))
            ->with(relations: 'author');

        if ($id) {
            $this->info(string: "Specific pattern ID: {$id}");

            $q->where(column: 'id', operator: $id);
        }

        $count = $q->count();

        $this->info(string: "Total patterns found: {$count} with author: {$from}");

        $result = 0;

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns) use (&$toAuthor, &$fromAuthor, &$result): void {
                $pattern = $patterns->first();

                $this->info(string: "Detaching from author: {$fromAuthor->name} from pattern ID: {$pattern->id}");

                $pattern->author()->associate(model: $toAuthor);

                $pattern->save();

                $result++;
            },
        );

        $this->info(string: 'Pattern author replaced successfully, ' . $result . ' records updated.');
    }
}
