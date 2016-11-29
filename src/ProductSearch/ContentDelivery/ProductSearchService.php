<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnsupportedSortOrderException;
use LizardsAndPumpkins\ProductSearch\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ProductSearch\QueryOptions;

class ProductSearchService
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

    /**
     * @var int
     */
    private $maxAllowedProductsPerPage;

    /**
     * @var string[]
     */
    private $sortableAttributeCodes;

    public function __construct(
        DataPoolReader $dataPoolReader,
        ProductJsonService $productJsonService,
        int $maxAllowedProductsPerPage,
        string ...$sortableAttributeCodes
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonService = $productJsonService;
        $this->maxAllowedProductsPerPage = $maxAllowedProductsPerPage;
        $this->sortableAttributeCodes = $sortableAttributeCodes;
    }

    /**
     * @param string $queryString
     * @param Context $context
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortBy $sortBy
     * @return array[]
     */
    public function query(
        string $queryString,
        Context $context,
        int $rowsPerPage,
        int $pageNumber,
        SortBy $sortBy
    ) : array {
        $queryOptions = $this->createQueryOptions($context, $rowsPerPage, $pageNumber, $sortBy);
        $searchEngineResponse = $this->dataPoolReader->getSearchResultsMatchingString($queryString, $queryOptions);
        $productIds = $searchEngineResponse->getProductIds();

        if ([] === $productIds) {
            return ['total' => 0, 'data' => []];
        }

        return [
            'total' => $searchEngineResponse->getTotalNumberOfResults(),
            'data' => $this->productJsonService->get($context, ...$productIds)
        ];
    }

    private function createQueryOptions(
        Context $context,
        int $rowsPerPage,
        int $pageNumber,
        SortBy $sortBy
    ) : QueryOptions
    {
        $this->validateSortBy($sortBy);
        $this->validateRowsPerPage($rowsPerPage);

        $filterSelection = [];
        $facetFiltersToIncludeInResult = new FacetFiltersToIncludeInResult();

        return QueryOptions::create(
            $filterSelection,
            $context,
            $facetFiltersToIncludeInResult,
            $rowsPerPage,
            $pageNumber,
            $sortBy
        );
    }

    private function validateSortBy(SortBy $sortBy)
    {
        if (!in_array((string) $sortBy->getAttributeCode(), $this->sortableAttributeCodes)) {
            throw new UnsupportedSortOrderException(
                sprintf('Sorting by "%s" is not supported', $sortBy->getAttributeCode())
            );
        }
    }

    private function validateRowsPerPage(int $rowsPerPage)
    {
        if ($rowsPerPage > $this->maxAllowedProductsPerPage) {
            throw new InvalidNumberOfProductsPerPageException(sprintf(
                'Maximum allowed number of products per page is %d, got %d.',
                $this->maxAllowedProductsPerPage,
                $rowsPerPage
            ));
        }
    }
}
