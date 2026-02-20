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

        $this->info('Fixing patterns with author in title...');

        $id = $this->option('id');

        $regexp = '[А-Яа-яA-Za-z]+$';

        $replaceFilter = config('tag_to_author_swap_filter');

        $q = Pattern::query()
            ->select(
                'id',
                'title',
                'source',
                DB::raw("REGEXP_SUBSTR(title, 'от {$regexp}') AS author_with_ot"),
                DB::raw("REGEXP_SUBSTR(title, '{$regexp}') AS author_only")
            )
            ->whereRaw("title REGEXP 'от {$regexp}'")
            ->whereNull('author_id');

        if ($id) {
            $q->where('id', $id);
        }

        $q->chunkById(100, function ($patterns) use (&$replaceFilter, &$regexp): void {
            foreach ($patterns as $pattern) {
                $authorName = $pattern->author_only;

                $this->info("Found author in pattern: {$authorName}");

                $loweredAuthorName = mb_strtolower($authorName);
                $newAuthorName = $authorName;

                $this->info("Trying to find if author name '{$authorName}' is in the replace filter...");

                foreach ($replaceFilter as $replaceFilterItem) {
                    if (in_array($loweredAuthorName, $replaceFilterItem['tags'])) {
                        $newAuthorName = $replaceFilterItem['author'];

                        break;
                    }
                }

                if ($newAuthorName !== $authorName) {
                    $this->info("Author name changed from '{$authorName}' to '{$newAuthorName}'");
                }

                $author = PatternAuthor::query()
                    ->firstOrCreate([
                        'name' => $newAuthorName
                    ]);

                $pattern->author()->associate($author);

                $pattern->title = preg_replace("/от {$regexp}/", '', (string) $pattern->title);

                $pattern->save();
            }
        });

        $this->info('Successfully fixed patterns with author in title.');

        $newAuthors = PatternAuthor::query()
            ->where('created_at', '>=', $now)
            ->pluck('name')
            ->toArray();

        $newAuthorsCount = count($newAuthors);

        $this->info(
            "Successfully fixed patterns with author in title. {$newAuthorsCount} new authors created: " . implode(', ', $newAuthors)
        );
    }
}
