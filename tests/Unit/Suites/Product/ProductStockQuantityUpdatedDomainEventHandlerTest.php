<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

/**
 * @covers \Brera\Product\ProductStockQuantityUpdatedDomainEventHandler
 */
class ProductStockQuantityUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockQuantityUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEvent;

    /**
     * @var ProductStockQuantityProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductStockQuantitySourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStockQuantitySourceBuilder;

    /**
     * @var ProductStockQuantityUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $this->mockDomainEvent = $this->getMock(ProductStockQuantityUpdatedDomainEvent::class, [], [], '', false);

        $this->mockProjector = $this->getMock(ProductStockQuantityProjector::class, [], [], '', false);
        $this->mockProductStockQuantitySourceBuilder = $this->getMock(
            ProductStockQuantitySourceBuilder::class,
            [],
            [],
            '',
            false
        );
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        $this->domainEventHandler = new ProductStockQuantityUpdatedDomainEventHandler(
            $this->mockDomainEvent,
            $this->mockProductStockQuantitySourceBuilder,
            $stubContextSource,
            $this->mockProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProductQuantitySnippetProjectionIsTriggered()
    {
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->mockProductStockQuantitySourceBuilder->method('createFromXml')
            ->willReturn($stubProductStockQuantitySource);

        $this->mockProjector->expects($this->once())
            ->method('project');

        $this->domainEventHandler->process();
    }
}
