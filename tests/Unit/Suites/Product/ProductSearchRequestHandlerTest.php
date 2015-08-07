<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DefaultHttpResponse;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpUrl;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;

/**
 * @covers \Brera\Product\ProductSearchRequestHandler
 * @uses   \Brera\Product\ProductSearchResultsMetaSnippetContent
 */
class ProductSearchRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpUrl;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var ProductSearchRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpRequest;

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGeneratorLocator */
        $mockSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $mockSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($mockSnippetKeyGenerator);

        $this->requestHandler = new ProductSearchRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $mockSnippetKeyGeneratorLocator
        );

        $this->stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $this->stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->stubHttpRequest->method('getUrl')->willReturn($this->stubHttpUrl);
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl()
    {
        $urlString = 'foo';
        $this->stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubHttpRequest->method('getQueryParameter')
            ->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn('bar');

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $urlString = ProductSearchRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);
        $this->stubHttpRequest->method('getQueryParameter')
            ->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn('foo');

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringParameterIsNotPresent()
    {
        $urlString = ProductSearchRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpUrl->method('getQueryParameter')
            ->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn(null);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringIsShorterThenMinimalAllowedLength()
    {
        $urlString = ProductSearchRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpUrl->method('getQueryParameter')
            ->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn('f');
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanBeHandledIfValidSearchRequest()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);
        $this->assertTrue($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    /**
     * @depends testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl
     */
    public function testExceptionIsThrownDuringAttemptToProcessInvalidRequest()
    {
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubHttpRequest);
    }

    public function testHttpResponseIsReturned()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $this->mockPageBuilder->method('buildPage')
            ->willReturn($this->getMock(DefaultHttpResponse::class, [], [], '', false));

        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $this->mockDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));

        $this->assertInstanceOf(DefaultHttpResponse::class, $this->requestHandler->process($this->stubHttpRequest));
    }

    public function testSearchResultsAreAddedToPageBuilder()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $this->mockDataPoolReader->method('getSearchResults')->willReturn(['product_in_listing_id']);

        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $this->mockDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);


        $this->mockPageBuilder->expects($this->once())->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubHttpRequest);
    }

    /**
     * @param string $queryString
     */
    private function prepareStubHttpRequest($queryString)
    {
        $urlString = ProductSearchRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpUrl->method('getQueryParameter')
            ->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn($queryString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
    }
}
