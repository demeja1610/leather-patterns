<?php

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Models\PatternCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ReplacePatternCategoryForPatternsCommand extends Command
{
    protected $signature = 'tools:replace-pattern-category-for-patterns {--from=} {--to=} {--id=}';
    protected $description = 'Replace specified pattern category for another specified pattern category for all patterns with that category or for a specific pattern';

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

        $fromCategory = PatternCategory::query()->where('name', $from)->first();
        $toCategory = PatternCategory::query()->where('name', $to)->first();

        if (!$fromCategory || !$toCategory) {
            $this->error('Both from and to categories must exist.');

            return;
        }

        $this->info("From category ID: {$fromCategory->id}");
        $this->info("To category ID: {$toCategory->id}");

        $q = Pattern::query()
            ->whereHas('categories', fn($query) => $query->where('name', $from))
            ->with('categories');

        if ($id) {
            $this->info("Specific pattern ID: {$id}");

            $q->where('id', $id);
        }

        $count = $q->count();

        $this->info("Total patterns found: {$count} with category: {$from}");

        $result = 0;

        $q->chunkById(
            count: 1,
            callback: function (Collection $patterns) use (&$toCategory, &$fromCategory, &$result) {
                $pattern = $patterns->first();

                $this->info("Detaching from category: {$fromCategory->name} from pattern ID: {$pattern->id}");

                $pattern->categories()->detach($fromCategory);

                /**
                 * @var \Illuminate\Database\Eloquent\Collection $patternCategories
                 */
                $patternCategories = $pattern->categories;

                $alreadyHasCategory = $patternCategories->contains('name', '=', $toCategory->name);

                if ($alreadyHasCategory) {
                    $this->info("Pattern ID {$pattern->id} already has category: {$toCategory->name}, no need to attach it again");

                    return;
                }

                $pattern->categories()->attach($toCategory);

                $result++;
            }
        );

        $this->info('Pattern categories replaced successfully, ' . $result . ' records updated.');
    }
}
