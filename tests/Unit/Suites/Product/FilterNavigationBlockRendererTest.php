<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockStructure;
use Brera\ThemeLocator;

/**
 * @covers \Brera\Product\FilterNavigationBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 */
class FilterNavigationBlockRendererTest extends AbstractBlockRendererTest
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
        return new FilterNavigationBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }
}
