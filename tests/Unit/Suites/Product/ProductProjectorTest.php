<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\SampleContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\SnippetList;
use Brera\ProjectionSourceData;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductProjector
 */
class ProductProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetList;

    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRendererCollection;

    /**
     * @var ProductSearchDocumentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentBuilder;

    public function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn($this->stubSnippetList);

        $this->stubSearchDocumentBuilder = $this->getMock(ProductSearchDocumentBuilder::class, [], [], '', false);

        $this->projector = new ProductProjector(
            $this->mockRendererCollection,
            $this->stubSearchDocumentBuilder,
            $this->mockDataPoolWriter
        );
    }

    public function testSnippetListAndSearchDocumentAreSetOnDataPoolWriter()
    {
        $this->stubSearchDocumentBuilder->expects($this->once())
            ->method('aggregate')
            ->willReturn($this->stubSearchDocumentCollection);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($this->stubSnippetList);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSearchDocumentCollection')
            ->with($this->stubSearchDocumentCollection);

        $stubProduct = $this->getMock(ProductSource::class, [], [], '', false);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->projector->project($stubProduct, $stubContextSource);
    }

    public function testExceptionIsThrownIfTheDataSourceTypeIsNotProduct()
    {
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);

        $this->projector->project($invalidDataSourceType, $stubContextSource);
    }
}
