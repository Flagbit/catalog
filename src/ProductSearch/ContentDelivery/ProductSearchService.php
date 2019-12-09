<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
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

    public function __construct(
        DataPoolReader $dataPoolReader,
        ProductJsonService $productJsonService
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonService = $productJsonService;
    }

    public function query(
        SearchCriteria $searchCriteria,
        QueryOptions $queryOptions,
        string $snippetName
    ): ProductSearchResult {
        $searchEngineResponse = $this->dataPoolReader->getSearchResults($searchCriteria, $queryOptions);

        $productIds = $searchEngineResponse->getProductIds();
        $productData = $this->productJsonService->get($queryOptions->getContext(), $snippetName, ...$productIds);

        return new ProductSearchResult(
            $searchEngineResponse->getTotalNumberOfResults(),
            $productData,
            $searchEngineResponse->getFacetFieldCollection()
        );
    }
}
