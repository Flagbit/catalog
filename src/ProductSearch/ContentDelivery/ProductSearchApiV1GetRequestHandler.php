<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnableToProcessProductSearchRequestException;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnsupportedSortOrderException;
use LizardsAndPumpkins\ProductSearch\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;

class ProductSearchApiV1GetRequestHandler extends ApiRequestHandler
{
    const ENDPOINT_NAME = 'product';

    const QUERY_PARAMETER = 'q';

    const NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER = 'limit';

    const PAGE_NUMBER_PARAMETER = 'p';

    const SORT_ORDER_PARAMETER = 'order';

    const SORT_DIRECTION_PARAMETER = 'sort';

    /**
     * @var ProductSearchService
     */
    private $productSearchService;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var int
     */
    private $defaultNumberOfProductPerPage;

    /**
     * @var int
     */
    private $maxAllowedProductsPerPage;

    /**
     * @var SortBy
     */
    private $defaultSortBy;

    /**
     * @var string[]
     */
    private $sortableAttributeCodes;

    public function __construct(
        ProductSearchService $productSearchService,
        ContextBuilder $contextBuilder,
        int $defaultNumberOfProductPerPage,
        int $maxAllowedProductsPerPage,
        SortBy $defaultSortBy,
        string ...$sortableAttributeCodes
    ) {
        $this->productSearchService = $productSearchService;
        $this->contextBuilder = $contextBuilder;
        $this->defaultNumberOfProductPerPage = $defaultNumberOfProductPerPage;
        $this->maxAllowedProductsPerPage = $maxAllowedProductsPerPage;
        $this->defaultSortBy = $defaultSortBy;
        $this->sortableAttributeCodes = $sortableAttributeCodes;
    }

    public function canProcess(HttpRequest $request) : bool
    {
        if ($request->getMethod() !== HttpRequest::METHOD_GET) {
            return false;
        }

        $parts = $this->getRequestPathParts($request);

        if (count($parts) !== 2 || self::ENDPOINT_NAME !== $parts[1]) {
            return false;
        }

        if (! $request->hasQueryParameter(self::QUERY_PARAMETER) ||
            '' === trim($request->getQueryParameter(self::QUERY_PARAMETER))
        ) {
            return false;
        }

        return true;
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        if (! $this->canProcess($request)) {
            throw new UnableToProcessProductSearchRequestException();
        }

        $queryString = $request->getQueryParameter(self::QUERY_PARAMETER);
        $searchCriteria = new SearchCriterionFullText($queryString);
        $queryOptions = $this->createQueryOptions($request);

        $searchResult = $this->productSearchService->query($searchCriteria, $queryOptions);

        $body = json_encode($searchResult);
        $headers = [];

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_OK);
    }

    /**
     * @param HttpRequest $request
     * @return string[]
     */
    private function getRequestPathParts(HttpRequest $request) : array
    {
        return explode('/', trim($request->getPathWithoutWebsitePrefix(), '/'));
    }

    private function createQueryOptions(HttpRequest $request) : QueryOptions
    {
        $filterSelection = [];

        $context = $this->contextBuilder->createFromRequest($request);

        $facetFiltersToIncludeInResult = new FacetFiltersToIncludeInResult();

        $rowsPerPage = $this->getNumberOfProductPerPage($request);
        $this->validateRowsPerPage($rowsPerPage);

        $pageNumber = $this->getPageNumber($request);

        $sortBy = $this->getSortBy($request);
        $this->validateSortBy($sortBy);

        return QueryOptions::create(
            $filterSelection,
            $context,
            $facetFiltersToIncludeInResult,
            $rowsPerPage,
            $pageNumber,
            $sortBy
        );
    }

    private function getNumberOfProductPerPage(HttpRequest $request) : int
    {
        if ($request->hasQueryParameter(self::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER)) {
            return (int) $request->getQueryParameter(self::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER);
        }

        return $this->defaultNumberOfProductPerPage;
    }

    private function getPageNumber(HttpRequest $request) : int
    {
        if ($request->hasQueryParameter(self::PAGE_NUMBER_PARAMETER)) {
            return (int) $request->getQueryParameter(self::PAGE_NUMBER_PARAMETER);
        }

        return 0;
    }

    private function getSortBy(HttpRequest $request) : SortBy
    {
        if ($request->hasQueryParameter(self::SORT_ORDER_PARAMETER)) {
            return new SortBy(
                AttributeCode::fromString($request->getQueryParameter(self::SORT_ORDER_PARAMETER)),
                SortDirection::create($this->getSortDirectionString($request))
            );
        }

        return $this->defaultSortBy;
    }

    private function getSortDirectionString(HttpRequest $request) : string
    {
        if ($request->hasQueryParameter(self::SORT_DIRECTION_PARAMETER)) {
            return $request->getQueryParameter(self::SORT_DIRECTION_PARAMETER);
        }

        return SortDirection::ASC;
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
