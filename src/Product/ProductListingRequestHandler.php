<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

class ProductListingRequestHandler implements HttpRequestHandler
{
    const PAGINATION_QUERY_PARAMETER_NAME = 'p';

    /**
     * @var ProductListingCriteriaSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var string[]
     */
    private $filterNavigationAttributeCodes;

    /**
     * @var
     */
    private $defaultNumberOfProductsPerPage;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param string[] $filterNavigationAttributeCodes
     * @param int $defaultNumberOfProductsPerPage
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        array $filterNavigationAttributeCodes,
        $defaultNumberOfProductsPerPage,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->filterNavigationAttributeCodes = $filterNavigationAttributeCodes;
        $this->defaultNumberOfProductsPerPage = $defaultNumberOfProductsPerPage;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        $this->loadPageMetaInfoSnippet($request);
        return (bool)$this->pageMetaInfo;
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

        $this->addProductListingContentToPage($request);

        $keyGeneratorParams = [
            'products_per_page' => $this->defaultNumberOfProductsPerPage,
            PageMetaInfoSnippetContent::URL_KEY => ltrim($request->getUrlPathRelativeToWebFront(), '/')
        ];

        return $this->pageBuilder->buildPage($this->pageMetaInfo, $this->context, $keyGeneratorParams);
    }

    private function loadPageMetaInfoSnippet(HttpRequest $request)
    {
        if (null !== $this->pageMetaInfo) {
            return;
        }

        $this->pageMetaInfo = false;
        $metaInfoSnippetKey = $this->getMetaInfoSnippetKey($request);
        $json = $this->getPageMetaInfoJsonIfExists($metaInfoSnippetKey);
        if ($json) {
            $this->pageMetaInfo = ProductListingCriteriaSnippetContent::fromJson($json);
        }
    }

    /**
     * @param string $metaInfoSnippetKey
     * @return string
     */
    private function getPageMetaInfoJsonIfExists($metaInfoSnippetKey)
    {
        try {
            $snippet = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        } catch (KeyNotFoundException $e) {
            $snippet = '';
        }
        return $snippet;
    }

    private function addProductsInListingToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        $documents = $searchDocumentCollection->getDocuments();

        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysForSearchDocuments(...$documents);
        $productSnippets = $this->dataPoolReader->getSnippets($productInListingSnippetKeys);

        $snippetKey = 'products_grid';
        $snippetContents = '[' . implode(',', $productSnippets) . ']';

        $this->addDynamicSnippetToPageBuilder($snippetKey, $snippetContents);
    }

    /**
     * @param HttpRequest $request
     * @return SearchEngineResponse
     */
    private function getSearchResultsMatchingCriteria(HttpRequest $request)
    {
        $selectedFilters = $this->getSelectedFilterValuesFromRequest($request);
        $originalCriteria = $this->pageMetaInfo->getSelectionCriteria();

        $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFilters);

        $currentPageNumber = max(0, $request->getQueryParameter(self::PAGINATION_QUERY_PARAMETER_NAME) - 1);
        $productsPerPage = (int) $this->defaultNumberOfProductsPerPage;

        return $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $criteria,
            $this->context,
            $this->filterNavigationAttributeCodes,
            $productsPerPage,
            $currentPageNumber
        );
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getProductInListingSnippetKeysForSearchDocuments(SearchDocument ...$searchDocuments)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInListingSnippetRenderer::CODE
        );
        return array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $searchDocument->getProductId()]);
        }, $searchDocuments);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getMetaInfoSnippetKey(HttpRequest $request)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductListingCriteriaSnippetRenderer::CODE
        );
        $urlKey = $request->getUrlPathRelativeToWebFront();
        $metaInfoSnippetKey = $keyGenerator->getKeyForContext(
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        return $metaInfoSnippetKey;
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $filters
     * @return SearchCriteria
     */
    private function applyFiltersToSelectionCriteria(SearchCriteria $originalCriteria, array $filters)
    {
        $filtersCriteriaArray = [];

        foreach ($filters as $filterCode => $filterOptionValues) {
            if (empty($filterOptionValues)) {
                continue;
            }

            $optionValuesCriteriaArray = array_map(function ($filterOptionValue) use ($filterCode) {
                return $this->searchCriteriaBuilder->fromRequestParameter($filterCode, $filterOptionValue);
            }, $filterOptionValues);

            $filterCriteria = CompositeSearchCriterion::createOr(...$optionValuesCriteriaArray);
            $filtersCriteriaArray[] = $filterCriteria;
        }

        if (empty($filtersCriteriaArray)) {
            return $originalCriteria;
        }

        $filtersCriteriaArray[] = $originalCriteria;
        return CompositeSearchCriterion::createAnd(...$filtersCriteriaArray);
    }

    /**
     * @param HttpRequest $request
     * @return array[]
     */
    private function getSelectedFilterValuesFromRequest(HttpRequest $request)
    {
        return array_reduce($this->filterNavigationAttributeCodes, function ($carry, $attributeCode) use ($request) {
            $carry[$attributeCode] = array_filter(explode(',', $request->getQueryParameter($attributeCode)));
            return $carry;
        }, []);
    }

    private function addProductListingContentToPage(HttpRequest $request)
    {
        $searchEngineResponse = $this->getSearchResultsMatchingCriteria($request);
        $searchDocumentCollection = $searchEngineResponse->getSearchDocuments();

        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

        $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
        $this->addProductsInListingToPageBuilder($searchDocumentCollection);
        $this->addPaginationSnippetsToPageBuilder($searchEngineResponse);
    }

    private function addFilterNavigationSnippetToPageBuilder(SearchEngineFacetFieldCollection $facetFieldCollection)
    {
        $snippetCode = 'filter_navigation';
        $snippetContents = json_encode($facetFieldCollection, JSON_PRETTY_PRINT);

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContents);
    }

    private function addPaginationSnippetsToPageBuilder(SearchEngineResponse $searchEngineResponse)
    {
        $this->addDynamicSnippetToPageBuilder(
            'total_number_of_results',
            $searchEngineResponse->getTotalNumberOfResults()
        );
        $this->addDynamicSnippetToPageBuilder('products_per_page', (int) $this->defaultNumberOfProductsPerPage);
    }

    /**
     * @param string $snippetCode
     * @param string $snippetContents
     */
    private function addDynamicSnippetToPageBuilder($snippetCode, $snippetContents)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }
}
