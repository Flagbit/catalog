<?php


namespace Brera\Http;

/**
 * @covers \Brera\Http\ResourceNotFoundRequestHandler
 */
class ResourceNotFoundRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceNotFoundRequestHandler
     */
    private $requestHandler;

    public function setUp()
    {
        $this->requestHandler = new ResourceNotFoundRequestHandler();
    }

    /**
     * @test
     */
    public function itShouldReturnAHttpResourceNotFoundResponse()
    {
        $result = $this->requestHandler->process();
        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnTrueForEveryRequest()
    {
        $this->assertTrue($this->requestHandler->canProcess());
    }
}
