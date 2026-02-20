<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Models\PatternTag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ReplacePatternTagForPatternsCommand extends Command
{
    protected $signature = 'tools:replace-pattern-tag-for-patterns {--from=} {--to=} {--id=}';

    protected $description = 'Replace specified pattern tag for another specified pattern tag for all patterns with that tag or for a specific pattern';

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

        $fromTag = PatternTag::query()->where(column: 'name', operator: $from)->first();
        $toTag = PatternTag::query()->where(column: 'name', operator: $to)->first();

        if (!$fromTag || !$toTag) {
            $this->error(string: 'Both from and to tags must exist.');

            return;
        }

        $this->info(string: "From tag ID: {$fromTag->id}");
        $this->info(string: "To tag ID: {$toTag->id}");

        $q = Pattern::query()
            ->whereHas(relation: 'tags', callback: fn($query) => $query->where(column: 'name', operator: $from))
            ->with(relations: 'tags');

        if ($id) {
            $this->info(string: "Specific pattern ID: {$id}");

            $q->where(column: 'id', operator: $id);
        }

        $count = $q->count();

        $this->info(string: "Total patterns found: {$count} with tag: {$from}");

        $result = 0;

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns) use (&$toTag, &$fromTag, &$result): void {
                $pattern = $patterns->first();

                $this->info(string: "Detaching from tag: {$fromTag->name} from pattern ID: {$pattern->id}");

                $pattern->tags()->detach(ids: $fromTag);

                /**
                 * @var \Illuminate\Database\Eloquent\Collection $patternTags
                 */
                $patternTags = $pattern->tags;

                $alreadyHasTag = $patternTags->contains(key: 'name', operator: '=', value: $toTag->name);

                if ($alreadyHasTag) {
                    $this->info(string: "Pattern ID {$pattern->id} already has tag: {$toTag->name}, no need to attach it again");

                    return;
                }

                $pattern->tags()->attach(ids: $toTag);

                $result++;
            },
        );

        $this->info(string: 'Pattern tags replaced successfully, ' . $result . ' records updated.');
    }
}
