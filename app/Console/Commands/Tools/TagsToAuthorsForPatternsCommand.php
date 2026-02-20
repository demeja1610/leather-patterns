<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Models\PatternTag;
use App\Models\PatternAuthor;
use Illuminate\Console\Command;

class TagsToAuthorsForPatternsCommand extends Command
{
    protected $signature = 'tools:tags-to-authors-for-patterns';

    protected $description = 'Associate tags with authors for patterns';

    public function handle(): void
    {
        $this->info(string: 'Running Tags to Authors for Patterns Command...');

        $data = $this->getData();

        $createdAuthors = [];

        foreach ($data as $item) {
            $tagNames = array_map(
                callback: mb_strtolower(...),
                array: $item['tags'],
            );

            $authorName = $item['author'];

            $this->info(string: "Processing tags for author: {$authorName}");

            $author = PatternAuthor::query()
                ->where(column: 'name', operator: $authorName)
                ->first();

            if (!$author) {
                $author = PatternAuthor::query()
                    ->create(attributes: [
                        'name' => $authorName,
                    ]);

                $createdAuthors[] = $author;
            }

            $tags = PatternTag::query()
                ->whereIn('name', $tagNames)
                ->get();

            foreach ($tags as $tag) {
                $this->info(string: "Processing tag: {$tag->name}");

                $patterns = Pattern::query()
                    ->whereHas(relation: 'tags', callback: function ($query) use ($tag): void {
                        $query->where(column: 'pattern_tags.id', operator: $tag->id);
                    })->get();

                /** @var Pattern $pattern */
                foreach ($patterns as $pattern) {
                    $this->info(string: "Processing pattern: {$pattern->id}");

                    $this->info(string: "Associating author: {$author->name} to pattern: {$pattern->id}");

                    $pattern->author()->associate(model: $author);

                    $pattern->save();

                    $this->info(string: "Detaching tag: {$tag->name} from pattern: {$pattern->id}");

                    $pattern->tags()->detach(ids: $tag);
                }
            }
        }

        $this->info(string: 'Successfully associated tags with authors for patterns.');

        $this->info(
            string: count(value: $createdAuthors) . ' authors were created: ' . implode(
                separator: ', ',
                array: array_map(
                    callback: fn(PatternAuthor $author) => $author->name,
                    array: $createdAuthors,
                ),
            ),
        );
    }

    protected function getData(): array
    {
        return config(key: 'tag_to_author_swap_filter');
    }
}
