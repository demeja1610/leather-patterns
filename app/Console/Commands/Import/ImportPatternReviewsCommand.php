<?php

declare(strict_types=1);

namespace App\Console\Commands\Import;

use App\Console\Commands\Command;
use Illuminate\Support\Facades\DB;

class ImportPatternReviewsCommand extends Command
{
    protected $signature = 'import:pattern-reviews';

    protected $description = 'Import pattern reviews';

    public function handle(): void
    {
        $this->info('Importing pattern reviews...');

        DB::connection('mysql_import')->table('pattern_reviews')
            ->select([
                'pattern_reviews.id',
                'pattern_reviews.reviewer_name',
                'pattern_reviews.rating',
                'pattern_reviews.review',
                'pattern_reviews.review_date',
                'pattern_reviews.approved',
                'pattern_reviews.user_id',
                'pattern_reviews.pattern_id',
            ])
            ->orderBy('pattern_reviews.id')
            ->chunk(
                count: 500,
                callback: function ($chunk): void {
                    $from = $chunk->first()->id;
                    $to = $chunk->last()->id;
                    $count = $chunk->count();

                    $this->info("Importing {$count} pattern reviews from {$from} to {$to}.");

                    $toInsert = [];

                    foreach ($chunk as $item) {
                        $toInsert[] = [
                            'reviewer_name' => $item->reviewer_name,
                            'rating' => $item->rating,
                            'comment' => $item->review,
                            'reviewed_at' => $item->review_date,
                            'is_approved' => $item->approved,
                            'user_id' => $item->user_id,
                            'pattern_id' => $item->pattern_id,
                        ];
                    }

                    DB::table('pattern_reviews')->insert($toInsert);
                }
            );

        $this->info("All pattern reviews imported successfully.");
    }
}
