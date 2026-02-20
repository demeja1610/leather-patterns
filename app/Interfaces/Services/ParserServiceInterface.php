<?php

declare(strict_types=1);

namespace App\Interfaces\Services;

use DOMXPath;
use Exception;
use DOMDocument;
use GuzzleHttp\Client;

interface ParserServiceInterface
{
    public function getClient(): Client;

    /**
     * @throws Exception
     */
    public function parseUrl(string $url): string;

    public function parseDOM(string $html): DOMDocument;

    public function getDOMXPath(DOMDocument $dom): DOMXPath;

    public function getRequiredHeaders(): array;

    public function getYoutubeVideoIdsFromString(string $string): array;

    public function getVkVideoIdsFromString(string $string): array;
}
