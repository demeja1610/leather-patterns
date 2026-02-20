<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\SourceAdapters;

use Exception;
use DOMElement;
use App\Enum\PatternSourceEnum;
use App\Interfaces\Services\ParserServiceInterface;

class FirstKozhaSourceAdapter extends AbstractSourceAdapter
{
    public function __construct(
        protected ParserServiceInterface $parserService
    ) {}

    public function processSource(string $baseURL): void
    {
        $url = trim(string: $baseURL, characters: '/') . '/vykrojki-shablony-new.html';

        $start = 0;

        while ($start !== null) {
            $this->info(message: "Processing from: {$start}");

            $requestUrl = $start > 0
                ? $url . '?start=' . $start
                : $url;

            try {
                $html = $this->parserService->parseUrl($requestUrl);
            } catch (Exception $e) {
                $this->error(message: "Error processing from {$start}: " . $e->getMessage());

                return;
            }

            $dom = $this->parserService->parseDOM($html);
            $xpath = $this->parserService->getDOMXPath($dom);

            $patternLinks = $xpath->query(expression: "//*[contains(@class, 'entry-header')]//h2//a");

            $this->info(message: "Found {$patternLinks->length} patterns");

            $patterns = [];

            foreach ($patternLinks as $patternLink) {
                if ($patternLink instanceof DOMElement) {
                    $patterns[] = $this->preparePatternForCreation(
                        url: "{$baseURL}/" . trim(string: $patternLink->getAttribute(qualifiedName: 'href'), characters: '/'),
                        source: PatternSourceEnum::FIRST_KOZHA,
                    );
                }
            }

            $patternsCount = count(value: $patterns);

            $savedCount = $this->createNewPatterns(patterns: $patterns);

            $this->success(message: "Saved {$savedCount} patterns");

            if ($savedCount !== $patternsCount) {
                $this->success(message: "The rest of the patterns are already exists, skipping to next source");

                $start = null;

                break;
            }

            $nextPageElement = $xpath->query(expression: "//a[contains(text(), '⇢')]");

            if ($nextPageElement->length > 0) {
                $nextPageItem = $nextPageElement->item(0);

                if ($nextPageItem instanceof DOMElement) {
                    $nextPageUrl = $nextPageItem->getAttribute(qualifiedName: 'href');

                    $nextPage = (int) explode(separator: '=', string: $nextPageUrl)[1];
                } else {
                    $nextPage = false;
                }
            } else {
                $nextPage = false;
            }

            if ($nextPage === false) {
                $this->success(message: "Link to next page not found, skipping to next source");

                $start = null;

                break;
            }

            $start = $nextPage;
        }
    }

    protected function getParseUrls(string $baseURL): ?array
    {
        $url = "{$baseURL}/fajly/besplatnye-vykrojki";

        $urls = [];

        try {
            $html = $this->parserService->parseUrl($url);
        } catch (Exception $exception) {
            $this->error(
                message: "Error while getting parse urls from {$url}: " . $exception->getMessage()
            );

            return null;
        }

        $dom = $this->parserService->parseDOM($html);
        $xpath = $this->parserService->getDOMXPath($dom);

        $productLinks = $xpath->query(expression: "//*[contains(@class, 'com-content-categories__item-title')]//a");

        foreach ($productLinks as $link) {
            if (!$link instanceof DOMElement) {
                continue;
            }

            if (trim(string: $link->textContent) === 'Модели для 3D печати') {
                continue;
            }

            $urls[] = "{$baseURL}/" . trim(string: $link->getAttribute(qualifiedName: 'href'), characters: '/');
        }

        return $urls;
    }
}
