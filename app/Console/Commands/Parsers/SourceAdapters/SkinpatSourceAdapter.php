<?php

namespace App\Console\Commands\Parsers\SourceAdapters;

use Exception;
use DOMElement;
use App\Enum\PatternSourceEnum;
use App\Interfaces\Services\ParserServiceInterface;

class SkinpatSourceAdapter extends AbstractSourceAdapter
{
    public function __construct(
        protected ParserServiceInterface $parserService
    ) {}

    public function processSource(string $baseURL): void
    {
        $page = 1;

        $url = $baseURL;

        while ($page !== null) {
            $this->info("Processing page: {$page}");

            $requestUrl = $page > 1
                ? $url . '?_page=' . $page
                : $url;

            try {
                $html = $this->parserService->parseUrl($requestUrl);
            } catch (Exception $e) {
                $this->error("Error processing page {$page}: " . $e->getMessage());

                return;
            }

            $dom = $this->parserService->parseDOM($html);
            $xpath = $this->parserService->getDOMXPath($dom);

            $patternLinks = $xpath->query("//h4[contains(@class, 'pt-cv-title')]//a");

            $this->info("Found {$patternLinks->length} patterns");

            $patterns = [];

            foreach ($patternLinks as $patternLink) {
                if ($patternLink instanceof DOMElement) {
                    $patterns[] = $this->preparePatternForCreation(
                        url: $patternLink->getAttribute('href'),
                        source: PatternSourceEnum::SKINPAT,
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

            $nextPage = $xpath->query("//a[contains(text(), '›')]");

            if ($nextPage->length === 0) {
                $this->success("Link to next page not found, skipping to next source");

                $page = null;

                break;
            }

            $page++;
        }
    }
}
