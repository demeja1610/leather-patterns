<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\SourceAdapters;

use Exception;
use DOMElement;
use App\Enum\PatternSourceEnum;
use App\Interfaces\Services\ParserServiceInterface;

class AbzalaSourceAdapter extends AbstractSourceAdapter
{
    public function __construct(
        protected ParserServiceInterface $parserService
    ) {}

    public function processSource(string $baseURL): void
    {
        $urls = $this->getParseUrls($baseURL);

        if ($urls === null) {
            $this->error("Error getting parse urls for Abzala source");

            return;
        }

        foreach ($urls as $url) {
            $start = 0;

            while ($start !== null) {
                $this->info("Processing from: {$start}");

                $requestUrl = $start > 0
                    ? $url . '?start=' . $start
                    : $url;

                try {
                    $html = $this->parserService->parseUrl($requestUrl);
                } catch (Exception $e) {
                    $this->error("Error processing from {$start}: " . $e->getMessage());

                    return;
                }

                $dom = $this->parserService->parseDOM($html);
                $xpath = $this->parserService->getDOMXPath($dom);

                $patternLinks = $xpath->query("//*[contains(@class, 'page-header')]//a");

                $this->info("Found {$patternLinks->length} patterns");

                $patterns = [];

                foreach ($patternLinks as $patternLink) {
                    if ($patternLink instanceof DOMElement) {
                        $patterns[] = $this->preparePatternForCreation(
                            url: "{$baseURL}/" . trim($patternLink->getAttribute('href'), '/'),
                            source: PatternSourceEnum::ABZALA,
                        );
                    }
                }

                $patternsCount = count($patterns);

                $savedCount = $this->createNewPatterns($patterns);

                $this->success("Saved {$savedCount} patterns");

                if ($savedCount !== $patternsCount) {
                    $this->success("The rest of the patterns are already exists, skipping to next source");

                    $start = null;

                    break;
                }

                $nextPageElement = $xpath->query("//a[contains(@aria-label, 'Перейти на следующую страницу')]");

                if ($nextPageElement->length > 0) {
                    $nextPageItem = $nextPageElement->item(0);

                    if ($nextPageItem instanceof DOMElement) {
                        $nextPageUrl = $nextPageItem->getAttribute('href');

                        $nextPage = (int) explode('=', $nextPageUrl)[1];
                    } else {
                        $nextPage = false;
                    }
                } else {
                    $nextPage = false;
                }

                if ($nextPage === false) {
                    $this->success("Link to next page not found, skipping to next source");

                    $start = null;

                    break;
                }

                $start = $nextPage;
            }
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
                "Error while getting parse urls from {$url}: " . $exception->getMessage()
            );

            return null;
        }

        $dom = $this->parserService->parseDOM($html);
        $xpath = $this->parserService->getDOMXPath($dom);

        $productLinks = $xpath->query("//*[contains(@class, 'com-content-categories__item-title')]//a");

        foreach ($productLinks as $link) {
            if (!$link instanceof DOMElement) {
                continue;
            }

            if (trim($link->textContent) === 'Модели для 3D печати') {
                continue;
            }

            $urls[] = "{$baseURL}/" . trim($link->getAttribute('href'), '/');
        }

        return $urls;
    }
}
