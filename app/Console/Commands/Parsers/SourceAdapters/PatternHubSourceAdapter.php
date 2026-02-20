<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\SourceAdapters;

use Exception;
use DOMElement;
use App\Enum\PatternSourceEnum;
use App\Interfaces\Services\ParserServiceInterface;

class PatternHubSourceAdapter extends AbstractSourceAdapter
{
    public function __construct(
        protected ParserServiceInterface $parserService,
    ) {}

    public function processSource(string $baseURL): void
    {
        $page = 1;

        $url = trim(string: $baseURL, characters: '/');

        while ($page !== null) {
            $this->info(message: "Processing page: {$page}");

            $requestUrl = $page > 1
                ? $url . '/page/' . $page . '/'
                : $url;

            try {
                $html = $this->parserService->parseUrl($requestUrl);
            } catch (Exception $e) {
                $this->error(message: "Error processing page {$page}: " . $e->getMessage());

                return;
            }

            $dom = $this->parserService->parseDOM($html);
            $xpath = $this->parserService->getDOMXPath($dom);

            $patternLinks = $xpath->query(expression: "//*[contains(@class, 'wp-block-post-featured-image')]//a");

            $this->info(message: "Found {$patternLinks->length} patterns");

            $patterns = [];

            foreach ($patternLinks as $patternLink) {
                if ($patternLink instanceof DOMElement) {
                    $patterns[] = $this->preparePatternForCreation(
                        url: $patternLink->getAttribute(qualifiedName: 'href'),
                        source: PatternSourceEnum::PATTERN_HUB,
                    );
                }
            }

            $patternsCount = count(value: $patterns);

            $savedCount = $this->createNewPatterns(patterns: $patterns);

            $this->success(message: "Saved {$savedCount} patterns");

            if ($savedCount !== $patternsCount) {
                $this->success(message: "The rest of the patterns are already exists, skipping to next source");

                $page = null;

                break;
            }

            $nextPage = $xpath->query(expression: "//a[contains(@class, 'wp-block-query-pagination-next')]");

            if ($nextPage->length === 0) {
                $this->success(message: "Link to next page not found, skipping to next source");

                $page = null;

                break;
            }

            $page++;
        }
    }
}
