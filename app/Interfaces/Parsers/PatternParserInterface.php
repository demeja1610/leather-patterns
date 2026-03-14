<?php

namespace App\Interfaces\Parsers;

use App\Models\Pattern;
use App\Interfaces\Services\ParserServiceInterface;

interface PatternParserInterface
{
    public function processPattern(Pattern $pattern): void;

    public function getParserService(): ParserServiceInterface;

    public function isRunningInConsole(): bool;
}
