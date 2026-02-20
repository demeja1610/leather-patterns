<?php

declare(strict_types=1);

namespace App\Console\Commands\Tools;

use App\Models\Pattern;
use App\Models\PatternAuthor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPatternsWithAuthorInTitleCommand extends Command
{
    protected $signature = 'tools:pattern:fix-patterns-with-author-in-title {--id=}';

    protected $description = 'Fix patterns with author in title';

    public function handle(): void
    {
        $now = now();

        $this->info(string: 'Fixing patterns with author in title...');

        $id = $this->option(key: 'id');

        $regexp = '[А-Яа-яA-Za-z]+$';

        $replaceFilter = config(key: 'tag_to_author_swap_filter');

        $q = Pattern::query()
            ->select(
                'id',
                'title',
                'source',
                DB::raw("REGEXP_SUBSTR(title, 'от {$regexp}') AS author_with_ot"),
                DB::raw("REGEXP_SUBSTR(title, '{$regexp}') AS author_only")
            )
            ->whereRaw(sql: "title REGEXP 'от {$regexp}'")
            ->whereNull(columns: 'author_id');

        if ($id) {
            $q->where(column: 'id', operator: $id);
        }

        $q->chunkById(count: 100, callback: function ($patterns) use (&$replaceFilter, &$regexp): void {
            foreach ($patterns as $pattern) {
                $authorName = $pattern->author_only;

                $this->info(string: "Found author in pattern: {$authorName}");

                $loweredAuthorName = mb_strtolower(string: $authorName);
                $newAuthorName = $authorName;

                $this->info(string: "Trying to find if author name '{$authorName}' is in the replace filter...");

                foreach ($replaceFilter as $replaceFilterItem) {
                    if (in_array(needle: $loweredAuthorName, haystack: $replaceFilterItem['tags'])) {
                        $newAuthorName = $replaceFilterItem['author'];

                        break;
                    }
                }

                if ($newAuthorName !== $authorName) {
                    $this->info(string: "Author name changed from '{$authorName}' to '{$newAuthorName}'");
                }

                $author = PatternAuthor::query()
                    ->firstOrCreate(attributes: [
                        'name' => $newAuthorName
                    ]);

                $pattern->author()->associate($author);

                $pattern->title = preg_replace(pattern: "/от {$regexp}/", replacement: '', subject: (string) $pattern->title);

                $pattern->save();
            }
        });

        $this->info(string: 'Successfully fixed patterns with author in title.');

        $newAuthors = PatternAuthor::query()
            ->where(column: 'created_at', operator: '>=', value: $now)
            ->pluck(column: 'name')
            ->toArray();

        $newAuthorsCount = count(value: $newAuthors);

        $this->info(
            string: "Successfully fixed patterns with author in title. {$newAuthorsCount} new authors created: " . implode(separator: ', ', array: $newAuthors)
        );
    }
}
