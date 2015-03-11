<?php

namespace Brera\Product;

use Brera\Renderer\BlockStructure;
use Brera\ThemeLocator;

/**
 * @covers \Brera\Product\ProductListingBlockRenderer
 * @uses \Brera\Renderer\BlockRenderer
 */
class ProductListingBlockRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnProductListingLayoutHandle()
    {
        $stubThemeLocator = $this->getMock(ThemeLocator::class);
        $stubBlockStructure = $this->getMock(BlockStructure::class);

        $blockRenderer = new ProductListingBlockRenderer($stubThemeLocator, $stubBlockStructure);

        $result = $blockRenderer->getRootSnippetCode();

        $this->assertEquals('product_listing', $result);
    }
}
