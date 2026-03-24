<?php

declare(strict_types=1);

namespace App\Parsers\PatternSource;

use Exception;
use DOMElement;
use App\Enum\PatternSourceEnum;
use App\Parsers\PatternSourceParser;
use App\Dto\Parser\Pattern\TagListDto;
use App\Dto\Parser\Pattern\CategoryListDto;
use App\Dto\Parser\Pattern\ParsedPatternLinkDto;
use App\Interfaces\Services\ParserServiceInterface;
use App\Interfaces\Parsers\PatternSourceParserInterface;

class AbzalaSourceParser extends PatternSourceParser implements PatternSourceParserInterface
{
    private readonly PatternSourceEnum $patternSource;

    public function __construct(ParserServiceInterface $parserService)
    {
        $this->patternSource = PatternSourceEnum::ABZALA;

        return parent::__construct($parserService);
    }

    public function processSource(): void
    {
        $this->logGettingSourceUrl($this->patternSource);

        $baseUrl = config("parse_sources.{$this->patternSource->value}");

        if ($baseUrl === null) {
            $this->logEmptySourceUrl($this->patternSource);

            return;
        }

        $this->logPatternSourceUrl($this->patternSource, $baseUrl);

        $urls = $this->getParseUrls(baseUrl: $baseUrl);

        if ($urls === null || $urls === []) {
            $this->logEmptySourceUrl($this->patternSource);

            return;
        }

        foreach ($urls as $url) {
            $page = 0;

            while ($page !== null) {
                $requestUrl = $page > 0
                    ? $url . '?start=' . $page
                    : $url;

                $this->logProcessingUrl($requestUrl);

                try {
                    $html = $this->getParserService()->parseUrl($requestUrl);
                } catch (Exception $e) {
                    $this->logErrorProcessingUrl($requestUrl, $e);

                    return;
                }

                $dom = $this->getParserService()->parseDOM($html);
                $xpath = $this->getParserService()->getDOMXPath($dom);

                $patternsElements = $xpath->query("//*[contains(@class, 'page-header')]//a");

                if ($patternsElements->count() === 0) {
                    $this->logNoPatternsElementsOnPageWithUrl($requestUrl);

                    return;
                }

                $this->logFoundedPatternsElementsOnPageWithUrl($requestUrl, $patternsElements->count());

                $patterns = [];

                foreach ($patternsElements as $patternLink) {
                    if ($patternLink instanceof DOMElement) {
                        $patterns[] = new ParsedPatternLinkDto(
                            source: $this->patternSource,
                            sourceUrl: "{$baseUrl}/" . trim($patternLink->getAttribute('href'), '/'),
                            categories: new CategoryListDto(),
                            tags: new TagListDto(),
                        );
                    }
                }

                $totalCount = count($patterns);
                $createdCount = 0;
                $existingCount = 0;

                foreach ($patterns as $pattern) {
                    $patternExists = $this->isPatternExists($pattern);

                    if ($patternExists === false) {
                        $this->createPattern($pattern);

                        $createdCount += 1;
                    } else {
                        $existingCount += 1;
                    }
                }

                $this->logExistingAndCreatedPatternsCount($totalCount, $existingCount, $createdCount);

                if ($createdCount !== $patternsElements->length) {
                    $this->logAllUnknownPatternsParsed($this->patternSource);

                    $page = null;

                    break;
                }

                $nextPageElement = $xpath->query("//a[contains(@aria-label, 'Перейти на следующую страницу')]");

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
                    $this->logNextPageLinkNotFound($requestUrl);

                    $page = null;

                    break;
                }

                $page = $nextPage;
            }
        }
    }

    public function getPatternSource(): PatternSourceEnum
    {
        return $this->patternSource;
    }

    protected function getParseUrls(string &$baseUrl): ?array
    {
        $url = "{$baseUrl}/fajly/besplatnye-vykrojki";

        $urls = [];

        try {
            $html = $this->getParserService()->parseUrl($url);
        } catch (Exception $exception) {
            return null;
        }

        $dom = $this->getParserService()->parseDOM($html);
        $xpath = $this->getParserService()->getDOMXPath($dom);

        $productLinks = $xpath->query(expression: "//*[contains(@class, 'com-content-categories__item-title')]//a");

        foreach ($productLinks as $link) {
            if (!$link instanceof DOMElement) {
                continue;
            }

            if (trim(string: $link->textContent) === 'Модели для 3D печати') {
                continue;
            }

            $urls[] = "{$baseUrl}/" . trim(string: $link->getAttribute(qualifiedName: 'href'), characters: '/');
        }

        return $urls;
    }
}
