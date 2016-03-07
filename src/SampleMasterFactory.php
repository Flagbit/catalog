<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\RegistrySnippetKeyGeneratorLocatorStrategy;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getCommandQueue
 * @method Queue\Queue getEventQueue
 * @method Context\Context getContext
 * @method Context\ContextSource createContextSource
 * @method Context\ContextBuilder createContextBuilder
 * @method DomainEventConsumer createDomainEventConsumer
 * @method CommandConsumer createCommandConsumer
 * @method RegistrySnippetKeyGeneratorLocatorStrategy createRegistrySnippetKeyGeneratorLocatorStrategy
 * @method SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method Log\InMemoryLogger getLogger
 * @method GenericSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 * @method ContentBlockSnippetKeyGeneratorLocatorStrategy createContentBlockSnippetKeyGeneratorLocatorStrategy
 * @method GenericSnippetKeyGenerator createProductSearchResultMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingTemplateSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductDetailPageMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockInProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductInSearchAutosuggestionSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductSearchAutosuggestionMetaSnippetKeyGenerator
 * @method string[] getRequiredContextParts
 * @method Projection\Catalog\Import\ProductXmlToProductBuilderLocator createProductXmlToProductBuilderLocator
 * @method Context\Context createContext
 * @method DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder createSearchCriteriaBuilder
 * @method string[] getSearchableAttributeCodes
 * @method DataPool\SearchEngine\FacetFiltersToIncludeInResult createProductListingFacetFiltersToIncludeInResult
 * @method ContentDelivery\Catalog\ProductsPerPage getProductsPerPageConfig
 * @method ContentDelivery\Catalog\SortOrderConfig[] getProductListingSortOrderConfig
 * @method ContentDelivery\Catalog\SortOrderConfig[] getProductSearchSortOrderConfig
 * @method ContentDelivery\Catalog\SortOrderConfig getProductSearchAutosuggestionSortOrderConfig
 * @method ContentDelivery\Catalog\ProductListingRequestHandler createProductListingRequestHandler
 * @method ContentDelivery\Catalog\ProductSearchRequestHandler createProductSearchRequestHandler
 * @method TwentyOneRunTaxableCountries createTaxableCountries
 * @method DataPool\SearchEngine\SearchEngine getSearchEngine
 * @method callable getProductDetailsViewTranslatorFactory
 * @method Renderer\Translation\TranslatorRegistry getTranslatorRegistry
 * @method SnippetKeyGenerator createProductListingTitleSnippetKeyGenerator
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
