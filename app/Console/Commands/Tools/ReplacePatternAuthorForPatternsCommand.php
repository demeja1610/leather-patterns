<?php

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Models\PatternAuthor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ReplacePatternAuthorForPatternsCommand extends Command
{
    protected $signature = 'tools:replace-pattern-author-for-patterns {--from=} {--to=} {--id=}';
    protected $description = 'Replace specified pattern author for another specified pattern author for all patterns with that author or for a specific pattern';

    public function handle()
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $id = $this->option('id');

        if (!$from || !$to) {
            $this->error('Both --from and --to options are required.');

            return;
        }

        $from = mb_strtolower($from);
        $to = mb_strtolower($to);

        $fromAuthor = PatternAuthor::query()->where('name', $from)->first();
        $toAuthor = PatternAuthor::query()->where('name', $to)->first();

        if (!$fromAuthor || !$toAuthor) {
            $this->error('Both from and to authors must exist.');

            return;
        }

        $this->info("From author ID: {$fromAuthor->id}");
        $this->info("To author ID: {$toAuthor->id}");

        $q = Pattern::query()
            ->whereHas('author', fn($query) => $query->where('name', $from))
            ->with('author');

        if ($id) {
            $this->info("Specific pattern ID: {$id}");

            $q->where('id', $id);
        }

        $count = $q->count();

        $this->info("Total patterns found: {$count} with author: {$from}");

        $result = 0;

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns) use (&$toAuthor, &$fromAuthor, &$result) {
                $pattern = $patterns->first();

                $this->info("Detaching from author: {$fromAuthor->name} from pattern ID: {$pattern->id}");

                $pattern->author()->associate($toAuthor);

                $pattern->save();

                $result++;
            }
        );

        $this->info('Pattern author replaced successfully, ' . $result . ' records updated.');
    }
}
