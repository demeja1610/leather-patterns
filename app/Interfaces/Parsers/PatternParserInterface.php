<?php

namespace App\Interfaces\Parsers;

use App\Models\Pattern;
use App\Interfaces\Services\FileServiceInterface;
use App\Interfaces\Services\ParserServiceInterface;

interface PatternParserInterface
{
    public function processPattern(Pattern $pattern): void;

    public function getParserService(): ParserServiceInterface;

    public function getFileService(): FileServiceInterface;

    public function isRunningInConsole(): bool;
}
