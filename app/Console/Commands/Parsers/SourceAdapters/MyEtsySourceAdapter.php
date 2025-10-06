<?php

namespace App\Console\Commands\Parsers\SourceAdapters;

use Exception;
use DOMElement;
use App\Enum\PatternSourceEnum;
use App\Interfaces\Services\ParserServiceInterface;

class MyEtsySourceAdapter extends AbstractSourceAdapter
{
    public function __construct(
        protected ParserServiceInterface $parserService
    ) {}

    public function processSource(string $baseURL): void
    {
        $page = 1;

        $url = trim($baseURL, '/') . '/product-category/vykrojki-iz-kozhi/';

        while ($page !== null) {
            $this->info("Processing page: {$page}");

            $requestUrl = $page > 1
                ? $url . '/page/' . $page . '/'
                : $url;

            try {
                $html = $this->parserService->parseUrl($requestUrl);
            } catch (Exception $e) {
                $this->error("Error processing page {$page}: " . $e->getMessage());

                return;
            }

            $dom = $this->parserService->parseDOM($html);
            $xpath = $this->parserService->getDOMXPath($dom);

            $patternLinks = $xpath->query("//a[contains(@class, 'woocommerce-loop-product__link')]");

            $this->info("Found {$patternLinks->length} patterns");

            $patterns = [];

            foreach ($patternLinks as $patternLink) {
                if ($patternLink instanceof DOMElement) {
                    $patterns[] = $this->preparePatternForCreation(
                        url: $patternLink->getAttribute('href'),
                        source: PatternSourceEnum::MYETSY,
                    );
                }
            }

            $patternsCount = count($patterns);

            $savedCount = $this->createNewPatterns($patterns);

            $this->success("Saved {$savedCount} patterns");

            if ($savedCount !== $patternsCount) {
                $this->success("The rest of the patterns are already exists, skipping to next source");

                $page = null;

                break;
            }

            $nextPage = $xpath->query("//a[contains(@class, 'next')]");

            if ($nextPage->length === 0) {
                $this->success("Link to next page not found, skipping to next source");

                $page = null;

                break;
            }

            $page++;
        }
    }
}
