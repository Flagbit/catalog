<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\SnippetResult;
use Brera\SnippetResultList;

/**
 * @covers Brera\Product\PriceSnippetRenderer
 * @uses   Brera\Product\Price
 * @uses   Brera\SnippetResult
 */
class PriceSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceSnippetRenderer
     */
    private $renderer;

    /**
     * @var ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductSource;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextSource;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

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
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new PriceSnippetRenderer(
            $this->mockSnippetResultList,
            $this->mockSnippetKeyGenerator,
            $this->dummyPriceAttributeCode
        );

        $this->mockContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContextMatrix', 'getAllAvailableContexts'])
            ->getMock();

        $this->mockProductSource = $this->getMock(ProductSource::class, [], [], '', false);
    }

    /**
     * @test
     */
    public function itShouldImplementSnippetRendererInterface()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    /**
     * @test
     */
    public function itShouldReturnEmptySnippetResultList()
    {
        $this->mockContextSource->expects($this->any())
            ->method('getAllAvailableContexts')
            ->willReturn([]);

        $result = $this->renderer->render($this->mockProductSource, $this->mockContextSource);

        $this->assertInstanceOf(SnippetResultList::class, $result);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function itShouldReturnSnippetResultListContainingASnippetResultWithAGivenKeyAndPrice()
    {
        $stubContext = $this->getMock(Context::class);
        $dummyPriceSnippetKey = 'bar';
        $dummyPriceAttributeValue = '1';

        $mockProduct = $this->getMock(Product::class, [], [], '', false);
        $mockProduct->expects($this->any())
            ->method('getAttributeValue')
            ->with($this->dummyPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);

        $this->mockProductSource->expects($this->any())
            ->method('getProductForContext')
            ->willReturn($mockProduct);

        $this->mockContextSource->expects($this->any())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($dummyPriceSnippetKey);

        $dummyPrice = Price::fromString($dummyPriceAttributeValue);
        $expectedSnippetResult = SnippetResult::create($dummyPriceSnippetKey, $dummyPrice->getAmount());

        $this->mockSnippetResultList->expects($this->once())
            ->method('add')
            ->with($expectedSnippetResult);

        $this->renderer->render($this->mockProductSource, $this->mockContextSource);
    }
}
