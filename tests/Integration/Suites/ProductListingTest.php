<?php

namespace Brera;

use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\ProductListingMetaInfoSnippetContent;
use Brera\Product\ProductListingRequestHandler;
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
        $this->importCatalog();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();

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
        $this->addPageTemplateWasUpdatedDomainEventToSetupProductListingFixture();
        $this->importCatalog();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
        
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductListingSnippetRenderer::CODE,
            $this->factory->createProductListingSnippetKeyGenerator()
        );

        $this->registerProductListingSnippetKeyGenerator();

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

    public function testContentBlockIsPresentAtProductListingPage()
    {
        $contentBlockContent = '<div>Content Block</div>';

        $this->addContentBlockToDataPool($contentBlockContent);
        $this->addRootTemplateChangedDomainEventToSetupProductListingFixture();
        $this->addProductImportDomainEventToSetUpProductFixture();
        $this->addProductListingCriteriaDomainDomainEventFixture();
        $this->registerContentBlockInProductListingSnippetKeyGenerator();

        $this->processDomainEvents();

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://www.example.com/foo'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $productListingRequestHandler = $this->getProductListingRequestHandler();
        $page = $productListingRequestHandler->process($httpRequest);
        $body = $page->getBody();

        $this->assertContains($contentBlockContent, $body);
    }

    private function addPageTemplateWasUpdatedDomainEventToSetupProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/v1/page_templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new SampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();
    }

    private function importCatalog()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/v1/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new SampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();
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
     * @param string $contentBlockContent
     */
    private function addContentBlockToDataPool($contentBlockContent)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/v1/content_blocks/in_product_listing_foo');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'language' => 'en_US']
        ]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new SampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->processCommandsInQueue();
        $this->processDomainEvents();
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

        $pageSnippetCodes = [
            'global_notices',
            'breadcrumbsContainer',
            'global_messages',
            'content_block_in_product_listing',
            'before_body_end'
        ];

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $searchCriteria,
            ProductListingSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return $metaSnippetContent->getInfo();
    }

    private function registerProductListingSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductListingSnippetRenderer::CODE,
            $this->factory->createProductListingSnippetKeyGenerator()
        );
    }

    private function registerContentBlockInProductListingSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            'content_block_in_product_listing',
            $this->factory->createContentBlockInProductListingSnippetKeyGenerator()
        );
    }
}
