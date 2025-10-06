<?php

namespace App\Console\Commands\Tools;

use App\Models\PatternTag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UCFirstTagsCommand extends Command
{
    protected $signature = 'tools:ucfirst-tags';
    protected $description = 'Perform ucfirst on tags';

    public function handle()
    {
        $this->info('Performing ucfirst on tags...');

        PatternTag::query()
            ->chunkById(
                count: 1,
                callback: function (Collection $tags) {
                    $tag = $tags->first();

                    $this->info('Processing tag: ' . $tag->name);

                    $tag->name = mb_ucfirst($tag->name);

                    $tag->save();
                }
            );
    }
}
