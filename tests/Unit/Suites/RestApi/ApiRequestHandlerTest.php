<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;

/**
 * @covers \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class ApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var ApiRequestHandler
     */
    private $apiRequestHandler;

    protected function setUp()
    {
        $this->stubRequest = $this->createMock(HttpRequest::class);
        $this->apiRequestHandler = new StubApiRequestHandler;
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->apiRequestHandler);
    }

    public function testInstanceOfGenericHttpResponseIsReturned()
    {
        $result = $this->apiRequestHandler->process($this->stubRequest);
        $this->assertInstanceOf(GenericHttpResponse::class, $result);
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testSetsCorsHeaders()
    {
        $response = $this->apiRequestHandler->process($this->stubRequest);
        $response->send();

        $expectedHeaders = [
            'access-control-allow-origin: *',
            'access-control-allow-methods: *',
            'content-type: application/json',
        ];

        $this->assertArraySubset($expectedHeaders, xdebug_get_headers());
    }

    public function testDummyBodyContentIsReturned()
    {
        $response = $this->apiRequestHandler->process($this->stubRequest);
        $result = $response->getBody();
        $expectedBodyContent = StubApiRequestHandler::DUMMY_BODY_CONTENT;

        $this->assertSame($expectedBodyContent, $result);
    }
}
