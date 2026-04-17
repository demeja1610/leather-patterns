<?php

namespace App\Jobs\Pattern;

use App\Models\Pattern;
use App\Models\PatternFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use App\Interfaces\Services\FileServiceInterface;

class DeleteUnarchivedPatternFilesJob implements ShouldQueue
{
    use Queueable;

    protected string $actionName = 'delete unarchived pattern(s) file(s)';

    protected FileServiceInterface $fileService;

    public function __construct(
        public ?int $patternId = null,
    ) {}

    public function handle(FileServiceInterface $fileService): void
    {
        $this->fileService = $fileService;

        Log::info("Start {$this->actionName}");

        $q = $this->getBaseQuery();

        if ($this->patternId !== null) {
            Log::info(ucfirst($this->actionName) . ". Pattern ID: {$this->patternId}");

            $q->where('patterns.id', $this->patternId);

            $pattern = $q->first();

            if ($pattern === null) {
                Log::info(
                    ucfirst($this->actionName) .
                        ". Specified pattern with ID: {$this->patternId} doesn't have any files to delete"
                );

                return;
            }

            $this->processPattern($pattern);
        } else {
            $i = 1;

            $q->chunkById(
                count: 250,
                callback: function (Collection $patterns) use (&$i): void {
                    $count = $patterns->count();

                    Log::info(ucfirst($this->actionName) . ", processing chunk: {$i} containing: {$count} patterns");

                    foreach ($patterns as $pattern) {
                        $this->processPattern($pattern);
                    }

                    $i++;
                },
                column: 'patterns.id',
                alias: 'id',
            );
        }

        Log::info("Finish {$this->actionName}");
    }

    protected function getBaseQuery(): Builder
    {
        $q =  Pattern::query()
            ->whereHas('files', fn(Builder $sq) => $sq->whereNotNull('parent_id'));

        $q->with([
            'files',
        ]);

        return $q;
    }

    protected function processPattern(Pattern &$pattern): void
    {
        Log::info(ucfirst($this->actionName) . " Processing pattern with ID: {$pattern->id}");

        $toDeleteFiles = $pattern->files->whereNotNull('parent_id');

        foreach ($toDeleteFiles as $file) {
            Log::info(ucfirst($this->actionName) . " Processing pattern file with ID: {$file->id}", [
                'file' => $file->toArray(),
            ]);

            $fileParent = $pattern->files->where('id', $file->parent_id)->first();

            if (!$fileParent instanceof PatternFile) {
                Log::error(
                    ucfirst($this->actionName) .
                        " File with ID: {$file->id}, cannot be deleted because its parent (ID: {$file->parent_id}) is not presented in pattern (ID: {$pattern->id}) files list",
                    [
                        'file' => $file->toArray(),
                    ]
                );

                continue;
            }

            $file->delete();
        }
    }
}
