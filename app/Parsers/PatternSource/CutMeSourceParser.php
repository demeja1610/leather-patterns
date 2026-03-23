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

class CutMeSourceParser extends PatternSourceParser implements PatternSourceParserInterface
{
    private readonly PatternSourceEnum $patternSource;

    public function __construct(ParserServiceInterface $parserService)
    {
        $this->patternSource = PatternSourceEnum::CUTME;

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

        $page = 1;

        $url = trim($baseUrl, '/') . '/product-category/vykrojki';

        while ($page !== null) {
            $requestUrl = $page > 1
                ? $url . '/page/' . $page . '/'
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

            $patternsElements = $xpath->query("//*[contains(@class, 'product-info-wrap')]//a[contains(@class, 'woocommerce-loop-product__link')]");

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
                        sourceUrl: $patternLink->getAttribute('href'),
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

            $nextPage = $xpath->query(expression: "//a[contains(@class, 'next')]");

            if ($nextPage->length === 0) {
                $this->logNextPageLinkNotFound($requestUrl);

                $page = null;

                break;
            }

            $page++;
        }
    }

    public function getPatternSource(): PatternSourceEnum
    {
        return $this->patternSource;
    }
}
