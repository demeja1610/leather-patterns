<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers;

use Illuminate\Console\Command;
use App\Jobs\Parser\ParsePatternsJob;
use App\Interfaces\Services\ParserServiceInterface;

class ParsePatternsCommand extends Command
{
    protected $signature = 'parsers:parse-patterns {--id=}';

    protected $description = 'Parse patterns';

    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $id = $this->option('id');

        dispatch_sync(new ParsePatternsJob($id ? (int) $id : null));

        return self::SUCCESS;
    }
}
