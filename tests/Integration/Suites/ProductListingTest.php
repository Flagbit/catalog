<?php

namespace Brera;

use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\ProductListingMetaInfoSnippetContent;
use Brera\Product\ProductListingRequestHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Utils\XPathParser;

class ProductListingTest extends AbstractIntegrationTest
{
    private $testUrl = 'http://example.com/adidas-men-accessories';

    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }
    
    public function testProductListingMetaSnippetIsWrittenIntoDataPool()
    {
        $this->addProductListingCriteriaDomainDomainEventFixture();
        $this->processDomainEvents(1);
        
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeyNode = (new XPathParser($xml))->getXmlNodesArrayByXPath('//catalog/listings/listing[1]/@url_key');
        $urlKey = $urlKeyNode[0]['value'];

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $productListingMetaInfoSnippetKeyGenerator = $this->factory->createProductListingMetaDataSnippetKeyGenerator();
        $snippetKey = $productListingMetaInfoSnippetKeyGenerator->getKeyForContext($context, ['url_key' => $urlKey]);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippet = $dataPoolReader->getSnippet($snippetKey);

        $expectedMetaInfoContent = json_encode($this->getStubMetaInfo());

        $this->assertSame($expectedMetaInfoContent, $metaInfoSnippet);
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->addRootTemplateChangedDomainEventToSetupProductListingFixture();
        $this->addProductImportDomainEventToSetUpProductFixture();
        $this->addProductListingCriteriaDomainDomainEventFixture();

        $this->processDomainEvents(5);
        
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductListingSnippetRenderer::CODE,
            $this->factory->createProductListingSnippetKeyGenerator()
        );

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://www.example.com'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $productListingRequestHandler = $this->getProductListingRequestHandler();
        $page = $productListingRequestHandler->process($httpRequest);
        $body = $page->getBody();

        /* TODO: read from XML */
        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
    
    private function addRootTemplateChangedDomainEventToSetupProductListingFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');
        $queue = $this->factory->getEventQueue();
        $queue->add(new RootTemplateChangedDomainEvent($xml));
    }

    private function addProductImportDomainEventToSetUpProductFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $queue = $this->factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));
    }

    private function addProductListingCriteriaDomainDomainEventFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $listingNodesRawXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('//catalog/listings/listing[1]');

        $queue = $this->factory->getEventQueue();
        $queue->add(new ProductListingSavedDomainEvent($listingNodesRawXml[0]));
    }

    /**
     * @return ProductListingRequestHandler
     */
    private function getProductListingRequestHandler()
    {
        $contextBuilder = $this->factory->createContextBuilder();
        $context = $contextBuilder->getContext(['website' => 'ru', 'language' => 'en_US']);
        $dataPoolReader = $this->factory->createDataPoolReader();
        $pageBuilder = new PageBuilder(
            $dataPoolReader,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->getLogger()
        );

        $url = HttpUrl::fromString($this->testUrl);
        $urlKey = $url->getPathRelativeToWebFront();

        $productListingMetaInfoSnippetKeyGenerator = $this->factory->createProductListingMetaDataSnippetKeyGenerator();
        $snippetKey = $productListingMetaInfoSnippetKeyGenerator->getKeyForContext($context, ['url_key' => $urlKey]);

        return new ProductListingRequestHandler(
            $snippetKey,
            $context,
            $dataPoolReader,
            $pageBuilder,
            $this->factory->getSnippetKeyGeneratorLocator()
        );
    }

    /**
     * @return mixed[]
     */
    private function getStubMetaInfo()
    {
        $searchCriterion1 = SearchCriterion::create('category', 'men-accessories', '=');
        $searchCriterion2 = SearchCriterion::create('brand', 'Adidas', '=');
        $searchCriteria = SearchCriteria::createAnd();
        $searchCriteria->add($searchCriterion1);
        $searchCriteria->add($searchCriterion2);

        $pageSnippetCodes = [];

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $searchCriteria,
            ProductListingSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return $metaSnippetContent->getInfo();
    }

    /**
     * @param int $numberOfMessages
     */
    private function processDomainEvents($numberOfMessages)
    {
        $consumer = $this->factory->createDomainEventConsumer();
        $consumer->process($numberOfMessages);
    }
}
