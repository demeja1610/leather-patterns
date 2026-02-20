<?php

declare(strict_types=1);

namespace App\Console\Commands\Parsers\SourceAdapters;

use Exception;
use DOMElement;
use App\Enum\PatternSourceEnum;
use App\Interfaces\Services\ParserServiceInterface;

class PablikKozhevnikaSourceAdapter extends AbstractSourceAdapter
{
    protected array $blacklistCategories = [
        'всё о коже',
        'журналы',
        'штампы для тиснения по коже',
        'файлы pdf',
        'мастер-класс',
        'тиснение по коже.',
        'рисунки',
        'эскизы',
    ];

    public function __construct(
        protected ParserServiceInterface $parserService
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

            $patternsElements = $xpath->query(expression: "//*[contains(@class, 'post-card__thumbnail')]");

            $this->info(message: "Found {$patternsElements->length} patterns");

            $patterns = [];

            foreach ($patternsElements as $patternsElement) {
                $patternLink = $xpath->query(expression: ".//a", contextNode: $patternsElement)->item(0);

                if ($patternLink instanceof DOMElement) {
                    $patternCategory = $xpath->query(expression: ".//span[contains(@class, 'post-card__category')]//span", contextNode: $patternsElement)->item(0);

                    if ($patternCategory instanceof DOMElement) {
                        $categoryText = $patternCategory->textContent;

                        if (in_array(needle: mb_strtolower(string: trim(string: $categoryText)), haystack: $this->blacklistCategories)) {
                            $this->warn(message: "Category '{$categoryText}' is blacklisted.");

                            continue;
                        }

                        $categories = array_map(
                            callback: trim(...),
                            array: explode(separator: ',', string: $categoryText)
                        );
                    }

                    $patterns[] = $this->preparePatternForCreation(
                        url: $patternLink->getAttribute(qualifiedName: 'href'),
                        source: PatternSourceEnum::PABLIK_KOZHEVNIKA,
                        categories: $categories ?? []
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

            $nextPage = $xpath->query(expression: "//a[contains(@class, 'next')]");

            if ($nextPage->length === 0) {
                $this->success(message: "Link to next page not found, skipping to next source");

                $page = null;

                break;
            }

            $page++;
        }
    }
}
