<?php

namespace Brera;

use Brera\DataPool\DataPoolWriter;

/**
 * @covers \Brera\TemplateProjector
 */
class TemplateProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetRendererCollection;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var TemplateProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->mockSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->projector = new TemplateProjector($this->mockSnippetRendererCollection, $this->mockDataPoolWriter);
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        $stubProjectionSourceData = $this->getMock(RootSnippetSourceList::class, [], [], '', false);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubSnippetList = $this->getMock(SnippetList::class);

        $this->mockSnippetRendererCollection->method('render')
            ->willReturn($stubSnippetList);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($stubSnippetList);

        $this->projector->project($stubProjectionSourceData, $stubContextSource);
    }

    public function testExceptionIsThrownIfProjectionDataIsNotInstanceOfRootSnippetSourceList()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);

        $this->projector->project($stubProjectionSourceData, $stubContextSource);
    }
}
