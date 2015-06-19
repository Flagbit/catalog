<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockRendererTestAbstract;
use Brera\Renderer\BlockStructure;
use Brera\Renderer\Stubs\StubBlock;
use Brera\ThemeLocator;

/**
 * @covers \Brera\Product\ProductDetailViewBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Renderer\BlockStructure
 * @uses   \Brera\Renderer\Block
 */
class ProductDetailViewBlockRendererTest extends BlockRendererTestAbstract
{
    /**
     * @param ThemeLocator|\PHPUnit_Framework_MockObject_MockObject $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @return BlockRenderer
     */
    protected function createRendererInstance(
        \PHPUnit_Framework_MockObject_MockObject $stubThemeLocator,
        BlockStructure $stubBlockStructure
    ) {
        return new ProductDetailViewBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }

    public function testLayoutHandleIsReturned()
    {
        $result = $this->getBlockRenderer()->getLayoutHandle();
        $this->assertEquals('product_detail_view', $result);
    }

    public function testProductPassedToRenderIsReturned()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubContext = $this->getStubContext();
        $template = $this->getUniqueTempDir() . '/template.phtml';
        $this->createFixtureFile($template, '');
        $this->addStubRootBlock(StubBlock::class, $template);
        $this->getBlockRenderer()->render($stubProduct, $stubContext);

        $this->assertSame($stubProduct, $this->getBlockRenderer()->getProduct());
    }
}
