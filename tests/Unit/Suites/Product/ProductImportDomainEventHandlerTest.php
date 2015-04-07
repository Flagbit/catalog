<?php

namespace Brera\Product;

use Brera\Context\ContextSource;

/**
 * @covers \Brera\Product\ProductImportDomainEventHandler
 */
class ProductImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldTriggerProjection()
    {
        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);

        $stubDomainEvent = $this->getMock(ProductImportDomainEvent::class, [], [], '', false);
        $stubDomainEvent->expects($this->once())
            ->method('getXml');

        $stubProductBuilder = $this->getMock(ProductSourceBuilder::class);
        $stubProductBuilder->expects($this->once())
            ->method('createProductSourceFromXml')
            ->willReturn($stubProductSource);

        $stubContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContextMatrix'])
            ->getMock();

        $stubProjector = $this->getMock(ProductProjector::class, [], [], '', false);
        $stubProjector->expects($this->once())
            ->method('project')
            ->with($stubProductSource, $stubContextSource);

        (new ProductImportDomainEventHandler(
            $stubDomainEvent,
            $stubProductBuilder,
            $stubContextSource,
            $stubProjector
        )
        )->process();
    }
}
