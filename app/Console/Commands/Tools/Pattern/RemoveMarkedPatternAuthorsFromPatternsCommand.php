<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\Pattern;

use App\Models\Pattern;
use Illuminate\Console\Command;

class RemoveMarkedPatternAuthorsFromPatternsCommand extends Command
{
    protected $signature = 'tools:pattern:remove-marked-pattern-authors-from-patterns';

    protected $description = 'Remove marked as `remove_on_appear` in DB pattern author from all patterns with that author';

    public function handle(): void
    {
        $q = Pattern::query()
            ->whereHas(relation: 'author', callback: fn($query) => $query->where('remove_on_appear', true));

        $count = $q->count();

        $this->info(string: "Total patterns found: {$count} with author marked as `remove_on_appear`");

        $result = $q->update(['author_id' => null]);

        $this->info(string: "Pattern authors removed successfully, {$result} pattern records were affected.");
    }
}
