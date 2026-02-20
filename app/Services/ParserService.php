<?php

declare(strict_types=1);

namespace App\Services;

use DOMXPath;
use Exception;
use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Interfaces\Services\ParserServiceInterface;

class ParserService implements ParserServiceInterface
{
    protected Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function parseDOM(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        return $dom;
    }

    public function getDOMXPath(DOMDocument $dom): DOMXPath
    {
        return new DOMXPath($dom);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @throws Exception
     */
    public function parseUrl($url): string
    {
        try {
            $response = $this->client->get($url, [
                'headers' => $this->getRequiredHeaders(),
                'allow_redirects' => true,
            ]);

            return $response->getBody()->getContents();
        } catch (GuzzleException $guzzleException) {
            throw new Exception(
                'HTTP request failed: ' . $guzzleException->getMessage(),
                $guzzleException->getCode(),
                $guzzleException
            );
        }
    }

    public function getRequiredHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];
    }

    public function getYoutubeVideoIdsFromString(string $string): array
    {
        preg_match_all(
            '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/|youtube-nocookie\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            $string,
            $matches,
        );

        return array_unique($matches[1]);
    }

    public function getVkVideoIdsFromString(string $string): array
    {
        $content = htmlspecialchars_decode($string);

        preg_match_all(
            '/video([-]?\d+_\d+)/',
            $content,
            $matches,
        );

        $ids = $matches[1];

        preg_match_all(
            '/[?&]oid=([-]?\d+)&id=(\d+)/',
            $content,
            $extMatches,
        );

        if (
            (isset($extMatches[1]) && $extMatches[1] !== []) &&
            (isset($extMatches[2]) && $extMatches[2] !== [])
        ) {
            foreach ($extMatches[1] as $key => $oid) {
                $id = $extMatches[2][$key];

                $ids[] = "{$oid}_{$id}";
            }
        }

        return array_unique($ids);
    }
}
