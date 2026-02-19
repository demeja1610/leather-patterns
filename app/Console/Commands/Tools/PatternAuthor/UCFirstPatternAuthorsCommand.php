<?php

namespace App\Console\Commands\Tools\PatternAuthor;

use App\Models\PatternAuthor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UCFirstPatternAuthorsCommand extends Command
{
    protected $signature = 'tools:pattern-author:ucfirst';
    protected $description = 'Perform ucfirst on pattern author';

    public function handle()
    {
        $this->info('Performing ucfirst on pattern author...');

        PatternAuthor::query()
            ->chunkById(
                count: 500,
                callback: function (Collection $authors) {
                    foreach ($authors as $author) {
                        $this->info('Processing pattern author: ' . $author->name);

                        $author->name = mb_ucfirst($author->name);

                        $author->save();
                    }
                }
            );
    }
}
