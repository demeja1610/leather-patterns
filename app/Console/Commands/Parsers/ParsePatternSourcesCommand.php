<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers;

use App\Enum\PatternSourceEnum;
use App\Console\Commands\Command;
use App\Jobs\Parser\ParsePatternSourcesJob;
use App\Interfaces\Services\ParserServiceInterface;

class ParsePatternSourcesCommand extends Command
{
    protected $signature = 'parsers:parse-pattern-sources {--source=}';

    protected $description = 'Parse pattern sources for patterns';

    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $sourceStr = $this->option(key: 'source');
        $source = null;

        if ($sourceStr !== null) {
            $source = PatternSourceEnum::tryFrom($sourceStr);

            if ($source === null) {
                $this->error("Please provide one of pattern source names presented in: " . PatternSourceEnum::class);

                return self::FAILURE;
            }
        }

        dispatch_sync(new ParsePatternSourcesJob($source));

        return self::SUCCESS;
    }
}
