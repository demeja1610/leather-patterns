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
        $from = $this->option('from');
        $to = $this->option('to');
        $id = $this->option('id');

        if (!$from || !$to) {
            $this->error('Both --from and --to options are required.');

            return;
        }

        $from = mb_strtolower($from);
        $to = mb_strtolower($to);

        $fromTag = PatternTag::query()->where('name', $from)->first();
        $toTag = PatternTag::query()->where('name', $to)->first();

        if (!$fromTag || !$toTag) {
            $this->error('Both from and to tags must exist.');

            return;
        }

        $this->info("From tag ID: {$fromTag->id}");
        $this->info("To tag ID: {$toTag->id}");

        $q = Pattern::query()
            ->whereHas('tags', fn($query) => $query->where('name', $from))
            ->with('tags');

        if ($id) {
            $this->info("Specific pattern ID: {$id}");

            $q->where('id', $id);
        }

        $count = $q->count();

        $this->info("Total patterns found: {$count} with tag: {$from}");

        $result = 0;

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns) use (&$toTag, &$fromTag, &$result): void {
                $pattern = $patterns->first();

                $this->info("Detaching from tag: {$fromTag->name} from pattern ID: {$pattern->id}");

                $pattern->tags()->detach($fromTag);

                /**
                 * @var \Illuminate\Database\Eloquent\Collection $patternTags
                 */
                $patternTags = $pattern->tags;

                $alreadyHasTag = $patternTags->contains('name', '=', $toTag->name);

                if ($alreadyHasTag) {
                    $this->info("Pattern ID {$pattern->id} already has tag: {$toTag->name}, no need to attach it again");

                    return;
                }

                $pattern->tags()->attach($toTag);

                $result++;
            }
        );

        $this->info('Pattern tags replaced successfully, ' . $result . ' records updated.');
    }
}
