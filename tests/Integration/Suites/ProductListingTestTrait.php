<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Queue\Queue;

trait ProductListingTestTrait
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    /**
     * @var SampleMasterFactory
     */
    private $factory;

    private function importCatalog()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    private function createProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    private function registerProductListingSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductListingPageSnippetRenderer::CODE,
            $this->factory->createProductListingSnippetKeyGenerator()
        );
    }

    /**
     * @return ProductListingRequestHandler
     */
    private function getProductListingRequestHandler()
    {
        $dataPoolReader = $this->factory->createDataPoolReader();
        $pageBuilder = new PageBuilder(
            $dataPoolReader,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->getLogger()
        );
        $filterNavigationConfig = $this->factory->getProductListingFilterNavigationConfig();
        $productsPerPage = $this->factory->getProductsPerPageConfig();
        $sortOrderConfigs = $this->factory->getProductListingSortOrderConfig();

        return new ProductListingRequestHandler(
            $this->factory->createContext(),
            $dataPoolReader,
            $pageBuilder,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $filterNavigationConfig,
            $productsPerPage,
            ...$sortOrderConfigs
        );
    }

    /**
     * @param HttpRequest $request
     * @return SampleMasterFactory
     */
    private function prepareIntegrationTestMasterFactoryForRequest(HttpRequest $request)
    {
        $masterFactory = new SampleMasterFactory;
        $masterFactory->register(new CommonFactory);
        $masterFactory->register($this->createIntegrationTestFactory($masterFactory));
        $masterFactory->register(new FrontendFactory($request));

        return $masterFactory;
    }

    /**
     * @param MasterFactory $masterFactory
     * @return IntegrationTestFactory
     */
    private function createIntegrationTestFactory(MasterFactory $masterFactory)
    {
        $factory = new IntegrationTestFactory();
        $factory->setMasterFactory($masterFactory);
        if ($this->isFirstInstantiationOfFactory()) {
            $this->storeInMemoryObjects($factory);
        } else {
            $this->persistInMemoryObjectsOnFactory($factory);
        }
        return $factory;
    }

    /**
     * @return bool
     */
    private function isFirstInstantiationOfFactory()
    {
        return null === $this->keyValueStore;
    }

    private function storeInMemoryObjects(IntegrationTestFactory $factory)
    {
        $this->keyValueStore = $factory->getKeyValueStore();
        $this->eventQueue = $factory->getEventQueue();
        $this->commandQueue = $factory->getCommandQueue();
        $this->searchEngine = $factory->getSearchEngine();
        $this->urlKeyStore = $factory->getUrlKeyStore();
    }

    private function persistInMemoryObjectsOnFactory(IntegrationTestFactory $factory)
    {
        $factory->setKeyValueStore($this->keyValueStore);
        $factory->setEventQueue($this->eventQueue);
        $factory->setCommandQueue($this->commandQueue);
        $factory->setSearchEngine($this->searchEngine);
        $factory->setUrlKeyStore($this->urlKeyStore);
    }
}
