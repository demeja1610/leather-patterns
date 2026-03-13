<?php

namespace App\Interfaces\Parsers;

use App\Enum\PatternSourceEnum;
use App\Dto\Parser\Pattern\ParsedPatternLinkDto;
use App\Interfaces\Services\ParserServiceInterface;

interface PatternSourceParserInterface
{
    public function processSource(): void;

    public function isPatternExists(ParsedPatternLinkDto $patternLink): bool;

    public function createPattern(ParsedPatternLinkDto $patternLink): void;

    public function getParserService(): ParserServiceInterface;

    public function getPatternSource(): PatternSourceEnum;

    public function isRunningInConsole(): bool;
}
