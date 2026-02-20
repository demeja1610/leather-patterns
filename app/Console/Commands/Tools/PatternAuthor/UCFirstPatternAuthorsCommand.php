<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools\PatternAuthor;

use App\Models\PatternAuthor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UCFirstPatternAuthorsCommand extends Command
{
    protected $signature = 'tools:pattern-author:ucfirst';

    protected $description = 'Perform ucfirst on pattern author';

    public function handle(): void
    {
        $this->info(string: 'Performing ucfirst on pattern author...');

        PatternAuthor::query()
            ->chunkById(
                count: 500,
                callback: function (Collection $authors): void {
                    foreach ($authors as $author) {
                        $this->info(string: 'Processing pattern author: ' . $author->name);

                        $author->name = mb_ucfirst(string: $author->name);

                        $author->save();
                    }
                },
            );
    }
}
