<?php

namespace App\Console\Commands\Import;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportPatternFilesCommand extends Command
{
    protected $signature = 'import:pattern-files';
    protected $description = 'Import pattern files';

    public function handle()
    {
        $this->info('Importing pattern files...');

       DB::connection('mysql_import')->table('file_pattern')
            ->join('files', 'file_pattern.file_id', '=', 'files.id')
            ->join('patterns', 'file_pattern.pattern_id', '=', 'patterns.id')
            ->where('patterns.source', '!=', PatternSourceEnum::SKINCUTS->value)
            ->orderBy('file_pattern.file_id')
            ->select([
                'file_pattern.file_id as file_id',
                'file_pattern.pattern_id as pattern_id',
                'files.path as file_path',
                'files.type as file_type',
                'files.extension as file_extension',
                'files.size as file_size',
                'files.mime_type as file_mime_type',
                'files.hash_algorithm as file_hash_algorithm',
                'files.hash as file_hash',
                'files.created_at as file_created_at',
                'files.updated_at as file_updated_at',
            ])
            ->chunk(
                count: 500,
                callback: function (Collection $chunk) {
                    $from = $chunk->first()->file_id;
                    $to = $chunk->last()->file_id;
                    $count = $chunk->count();

                    $this->info("Importing pattern files from {$from} to {$to} ({$count} total)...");

                    $toInsert = [];

                    foreach ($chunk as $item) {
                        $toInsert[] = [
                            'pattern_id' => $item->pattern_id,
                            'path' => $item->file_path,
                            'type' => $item->file_type,
                            'extension' => $item->file_extension,
                            'size' => $item->file_size,
                            'mime_type' => $item->file_mime_type,
                            'hash_algorithm' => $item->file_hash_algorithm,
                            'hash' => $item->file_hash,
                            'created_at' => $item->file_created_at,
                            'updated_at' => $item->file_updated_at,
                        ];
                    }

                    DB::table('pattern_files')->insert($toInsert);
                }
            );

        $this->info("All pattern files imported successfully.");
    }
}
