<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;

/**
 * @covers \LizardsAndPumpkins\Product\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\Price
 * @uses   \LizardsAndPumpkins\Snippet
 */
class PriceSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var string
     */
    private $dummyPriceAttributeCode = 'foo';

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new PriceSnippetRenderer(
            $this->mockSnippetList,
            $this->mockSnippetKeyGenerator,
            $this->dummyPriceAttributeCode
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testNothingIsAddedToSnippetListIfProductDoesNotHaveARequiredAttribute()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('hasAttribute')->with($this->dummyPriceAttributeCode)->willReturn(false);

        $this->mockSnippetList->expects($this->never())->method('add');

        $this->renderer->render($stubProduct);
    }

    public function testSnippetListContainingSnippetWithGivenKeyAndPriceIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $dummyPriceSnippetKey = 'bar';
        $dummyPriceAttributeValue = 1;

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('hasAttribute')->with($this->dummyPriceAttributeCode)->willReturn(true);
        $stubProduct->method('getContext')->willReturn($stubContext);
        $stubProduct->method('getFirstValueOfAttribute')->with($this->dummyPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);

        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummyPriceSnippetKey);

        $expectedSnippet = Snippet::create($dummyPriceSnippetKey, $dummyPriceAttributeValue);
        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($stubProduct);
    }
}
