<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;

/**
 * @covers \Brera\Product\ProductInListingInContextSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ProductInListingInContextSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testProductId = 2;
    
    /**
     * @var ProductInListingInContextSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ProductInListingBlockRenderer||\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductInListingBlockRenderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProduct()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->any())
            ->method('getId')
            ->willReturn($this->testProductId);
        return $stubProduct;
    }

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $this->stubProductInListingBlockRenderer = $this->getMockBuilder(ProductInListingBlockRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('render')
            ->willReturn('dummy content');
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('getRootSnippetCode')
            ->willReturn('dummy root block code');
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('getNestedSnippetCodes')
            ->willReturn([]);

        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductInListingInContextSnippetRenderer(
            $this->mockSnippetList,
            $this->stubProductInListingBlockRenderer,
            $this->mockSnippetKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldRenderProductInListingViewSnippets()
    {
        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn('stub-content-key');
        
        $this->mockSnippetList->expects($this->once())->method('add');

        $stubProduct = $this->getStubProduct();
        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->renderer->render($stubProduct, $stubContext);
    }

    /**
     * @test
     */
    public function itShouldDelegateToTheKeyGeneratorToFetchTheContextParts()
    {
        $testContextParts = ['version', 'website', 'language'];
        $this->mockSnippetKeyGenerator->expects($this->once())->method('getContextPartsUsedForKey')
            ->willReturn($testContextParts);

        $this->assertSame($testContextParts, $this->renderer->getUsedContextParts());
    }

    /**
     * @test
     */
    public function itShouldPassTheProductIdToTheKeyGenerator()
    {
        $stubContext = $this->getMock(Context::class);
        $stubProduct = $this->getStubProduct();

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn('stub-content-key');
        $this->mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), ['product_id' => $this->testProductId]);
        $this->renderer->render($stubProduct, $stubContext);
    }
}
