<?php

namespace Brera\PoC;

use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\PoC\Product\Product;

/**
 * @covers \Brera\PoC\PoCProductProjector
 */
class ProductProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldSetProductHtmlOnDataPoolWriter()
    {
        $stubProductSnippetRendererCollection
            = $this->getMock(ProductSnippetRendererCollection::class);
        $stubSnippetResultList = $this->getMock(SnippetResultList::class);

        $stubProductSnippetRendererCollection->expects($this->once())
            ->method('render')->willReturn($stubSnippetResultList);

        $stubDataPoolWriter = $this->getMockBuilder(DataPoolWriter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubDataPoolWriter->expects($this->once())
            ->method('writeSnippetResultList')
            ->with($stubSnippetResultList);

        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubEnvironment = $this->getMock(Environment::class);

        $projector = new ProductProjector(
            $stubProductSnippetRendererCollection,
            $stubDataPoolWriter
        );

        $projector->project($stubProduct, $stubEnvironment);
    }
}
