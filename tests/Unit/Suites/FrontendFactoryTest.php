<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Api\ApiRouter;
use LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\GenericHttpRouter;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpsUrl;
use LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler;

/**
 * @covers \LizardsAndPumpkins\FrontendFactory
 * @covers \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\SampleMasterFactory
 * @uses   \LizardsAndPumpkins\IntegrationTestFactory
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Product\FilterNavigationFilterCollection
 * @uses   \LizardsAndPumpkins\Http\GenericHttpRouter
 * @uses   \LizardsAndPumpkins\Product\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\Product\MultipleProductStockQuantityApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Api\ApiRouter
 * @uses   \LizardsAndPumpkins\Api\ApiRequestHandlerChain
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\PageBuilder
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductListingSourceListBuilder
 * @uses   \LizardsAndPumpkins\Utils\Directory
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \Brera\Renderer\Translation\TranslatorRegistry
 */
class FrontendFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrontendFactory
     */
    private $frontendFactory;

    public function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new IntegrationTestFactory());
        $masterFactory->register(new CommonFactory());

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpsUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->frontendFactory = new FrontendFactory($request);
        $masterFactory->register($this->frontendFactory);
    }

    public function testCatalogImportApiRequestHandlerIsReturned()
    {
        $result = $this->frontendFactory->createCatalogImportApiV1PutRequestHandler();
        $this->assertInstanceOf(CatalogImportApiV1PutRequestHandler::class, $result);
    }

    public function testContentBlocksApiRequestHandlerIsReturned()
    {
        $result = $this->frontendFactory->createContentBlocksApiV1PutRequestHandler();
        $this->assertInstanceOf(ContentBlocksApiV1PutRequestHandler::class, $result);
    }

    public function testApiRouterIsReturned()
    {
        $result = $this->frontendFactory->createApiRouter();
        $this->assertInstanceOf(ApiRouter::class, $result);
    }

    public function testProductDetailViewRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductDetailViewRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }

    public function testProductListingRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductListingRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }

    public function testSameKeyGeneratorLocatorIsReturnedViaGetter()
    {
        $result1 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $result2 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $result1);
        $this->assertSame($result1, $result2);
    }

    public function testItReturnsAContext()
    {
        $this->assertInstanceOf(Context::class, $this->frontendFactory->createContext());
    }

    public function testProductSearchResultRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductSearchResultRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }

    public function testItReturnsASimpleEuroPriceSnippetTransformation()
    {
        $result = $this->frontendFactory->createPriceSnippetTransformation();
        $this->assertInstanceOf(SimpleEuroPriceSnippetTransformation::class, $result);
    }

    public function testProductSearchAutosuggestionRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductSearchAutosuggestionRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }
}
