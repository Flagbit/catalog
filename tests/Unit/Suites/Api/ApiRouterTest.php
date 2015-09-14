<?php

namespace LizardsAndPumpkins\Api;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;

/**
 * @covers \LizardsAndPumpkins\Api\ApiRouter
 * @uses   \LizardsAndPumpkins\Http\HttpRequestHandler
 */
class ApiRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiRouter
     */
    private $apiRouter;

    /**
     * @var ApiRequestHandlerChain|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubApiRequestHandlerChain;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpRequest;

    protected function setUp()
    {
        $this->stubApiRequestHandlerChain = $this->getMock(ApiRequestHandlerChain::class);
        $this->apiRouter = new ApiRouter($this->stubApiRequestHandlerChain);

        $this->stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testNullIsReturnedIfUrlIsNotLedByApiPrefix()
    {
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('foo/bar');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfVersionFormatIsInvalid()
    {
        $this->stubHttpRequest->method('getHeader')->with('Accept')->willReturn('application/json');
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('api/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfEndpointCodeIsNotSpecified()
    {
        $this->stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn('application/vnd.lizards-and-pumpkins.foo.v1+json');
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('api');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfApiRequestHandlerCanNotProcessRequest()
    {
        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(false);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('api/foo');
        $this->stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn('application/vnd.lizards-and-pumpkins.foo.v1+json');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testApiRequestHandlerIsReturned()
    {
        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(true);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn('application/vnd.lizards-and-pumpkins.foo.v1+json');
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('api/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
