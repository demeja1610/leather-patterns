<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternTag;

use App\Models\PatternTag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UCFirstPatternCategoriesCommand extends Command
{
    protected $signature = 'tools:pattern-tag:ucfirst';

    protected $description = 'Perform ucfirst on pattern tags';

    public function handle(): void
    {
        $this->info(string: 'Performing ucfirst on pattern tags...');

        PatternTag::query()
            ->chunkById(
                count: 500,
                callback: function (Collection $tags): void {
                    foreach ($tags as $tag) {
                        $this->info(string: 'Processing pattern tag: ' . $tag->name);

                        $tag->name = mb_ucfirst(string: $tag->name);

                        $tag->save();
                    }
                },
            );
    }
}
