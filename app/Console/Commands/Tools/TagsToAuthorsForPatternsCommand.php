<?php

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Models\PatternTag;
use App\Models\PatternAuthor;
use Illuminate\Console\Command;

class TagsToAuthorsForPatternsCommand extends Command
{
    protected $signature = 'tools:tags-to-authors-for-patterns';
    protected $description = 'Associate tags with authors for patterns';

    public function handle()
    {
        $this->info('Running Tags to Authors for Patterns Command...');

        $data = $this->getData();

        $createdAuthors = [];

        foreach ($data as $item) {
            $tagNames = array_map('mb_strtolower', $item['tags']);
            $authorName = $item['author'];

            $this->info("Processing tags for author: $authorName");

            $author = PatternAuthor::query()->where('name', $authorName)->first();

            if (!$author) {
                $author = PatternAuthor::create(['name' => $authorName]);

                $createdAuthors[] = $author;
            }

            $tags = PatternTag::whereIn('name', $tagNames)->get();

            foreach ($tags as $tag) {
                $this->info("Processing tag: {$tag->name}");

                $patterns = Pattern::query()->whereHas('tags', function ($query) use ($tag) {
                    $query->where('pattern_tags.id', $tag->id);
                })->get();

                /** @var Pattern $pattern */
                foreach ($patterns as $pattern) {
                    $this->info("Processing pattern: {$pattern->id}");

                    $this->info("Associating author: {$author->name} to pattern: {$pattern->id}");

                    $pattern->author()->associate($author);

                    $pattern->save();

                    $this->info("Detaching tag: {$tag->name} from pattern: {$pattern->id}");

                    $pattern->tags()->detach($tag);
                }
            }
        }

        $this->info('Successfully associated tags with authors for patterns.');

        $this->info(count($createdAuthors) . ' authors were created: ' . implode(', ', array_map(fn($author) => $author->name, $createdAuthors)));
    }

    protected function getData(): array
    {
        return config('tag_to_author_swap_filter');
    }
}
