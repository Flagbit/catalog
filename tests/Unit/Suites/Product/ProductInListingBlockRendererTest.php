<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Renderer\AbstractBlockRendererTest;
use LizardsAndPumpkins\Renderer\BlockStructure;
use LizardsAndPumpkins\ThemeLocator;

/**
 * @covers \LizardsAndPumpkins\Product\ProductInListingBlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\BlockStructure
 * @uses   \LizardsAndPumpkins\Renderer\Block
 */
class ProductInListingBlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator|\PHPUnit_Framework_MockObject_MockObject $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @return BlockRenderer
     */
    final protected function createRendererInstance(
        \PHPUnit_Framework_MockObject_MockObject $stubThemeLocator,
        BlockStructure $stubBlockStructure
    ) {
        return new ProductInListingBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }

    public function testLayoutHandleIsReturned()
    {
        $result = $this->getBlockRenderer()->getLayoutHandle();
        $this->assertEquals('product_in_listing', $result);
    }
}
