<?php

namespace Brera;

use Brera\Context\ContextSource;

/**
 * @covers \Brera\TemplateWasUpdatedDomainEventHandler
 */
class TemplateWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Projector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var TemplateWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);

        /** @var TemplateWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(TemplateWasUpdatedDomainEvent::class, [], [], '', false);
        $stubDomainEvent->method('getProjectionSourceData')->willReturn($stubProjectionSourceData);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        $this->mockProjector = $this->getMock(Projector::class);

        /** @var TemplateProjectorLocator|\PHPUnit_Framework_MockObject_MockObject $stubTemplateProjectorLocator */
        $stubTemplateProjectorLocator = $this->getMock(TemplateProjectorLocator::class, [], [], '', false);
        $stubTemplateProjectorLocator->method('getTemplateProjectorForCode')->willReturn($this->mockProjector);

        $this->domainEventHandler = new TemplateWasUpdatedDomainEventHandler(
            $stubDomainEvent,
            $stubContextSource,
            $stubTemplateProjectorLocator
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProjectionIsTriggered()
    {
        $this->mockProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
