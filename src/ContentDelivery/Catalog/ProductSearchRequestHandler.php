<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

class ProductSearchRequestHandler implements HttpRequestHandler
{
    use ProductListingRequestHandlerTrait;

    const SEARCH_RESULTS_SLUG = 'catalogsearch/result';
    const QUERY_STRING_PARAMETER_NAME = 'q';
    const SEARCH_QUERY_MINIMUM_LENGTH = 3;

    /**
     * @var string[]
     */
    private $searchableAttributeCodes;

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param string[] $filterNavigationAttributeCodes
     * @param int $defaultNumberOfProductsPerPage
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string[] $searchableAttributeCodes
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        array $filterNavigationAttributeCodes,
        $defaultNumberOfProductsPerPage,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $searchableAttributeCodes
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->filterNavigationAttributeCodes = $filterNavigationAttributeCodes;
        $this->defaultNumberOfProductsPerPage = $defaultNumberOfProductsPerPage;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchableAttributeCodes = $searchableAttributeCodes;
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return $this->isValidSearchRequest($request);
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request)
    {
        if (!$this->canProcess($request)) {
            throw new UnableToHandleRequestException(sprintf('Unable to process request with handler %s', __CLASS__));
        }

        $searchEngineResponse = $this->getSearchResultsMatchingCriteria($request);
        $this->addProductListingContentToPage($searchEngineResponse);

        $metaInfoSnippetKeyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductSearchResultMetaSnippetRenderer::CODE
        );
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($metaInfoSnippetJson);

        $keyGeneratorParams = [
            'products_per_page' => $this->defaultNumberOfProductsPerPage
        ];

        return $this->pageBuilder->buildPage($metaInfoSnippetContent, $this->context, $keyGeneratorParams);
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    private function isValidSearchRequest(HttpRequest $request)
    {
        $urlPathWithoutTrailingSlash = rtrim($request->getUrlPathRelativeToWebFront(), '/');

        if (self::SEARCH_RESULTS_SLUG !== $urlPathWithoutTrailingSlash) {
            return false;
        }

        if (HttpRequest::METHOD_GET !== $request->getMethod()) {
            return false;
        }

        $searchQueryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);

        if (null === $searchQueryString || self::SEARCH_QUERY_MINIMUM_LENGTH > strlen($searchQueryString)) {
            return false;
        }

        return true;
    }

    /**
     * @param HttpRequest $request
     * @return SearchEngineResponse
     */
    private function getSearchResultsMatchingCriteria(HttpRequest $request)
    {
        $selectedFilters = $this->getSelectedFilterValuesFromRequest($request);

        $queryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);
        $originalCriteria = $this->searchCriteriaBuilder->anyOfFieldsContainString(
            $this->searchableAttributeCodes,
            $queryString
        );

        $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFilters);

        $currentPageNumber = $this->getCurrentPageNumber($request);
        $productsPerPage = (int) $this->defaultNumberOfProductsPerPage;

        return $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $criteria,
            $this->context,
            $this->filterNavigationAttributeCodes,
            $productsPerPage,
            $currentPageNumber
        );
    }
}
