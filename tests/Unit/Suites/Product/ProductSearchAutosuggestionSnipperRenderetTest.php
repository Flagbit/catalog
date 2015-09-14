<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetList
 */
class ProductSearchAutosuggestionSnipperRenderetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSearchAutosuggestionSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource
     */
    private $stubContextSource;

    protected function setUp()
    {
        $testSnippetList = new SnippetList;

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $this->snippetRenderer = new ProductSearchAutosuggestionSnippetRenderer(
            $testSnippetList,
            $stubSnippetKeyGenerator,
            $stubBlockRenderer
        );

        $stubContext = $this->getMock(Context::class);

        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testSnippetListIsReturned()
    {
        /** @var ProductListingSourceList|\PHPUnit_Framework_MockObject_MockObject $stubProductListingSourceList */
        $stubProductListingSourceList = $this->getMock(ProductListingSourceList::class, [], [], '', false);

        $result = $this->snippetRenderer->render($stubProductListingSourceList, $this->stubContextSource);

        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testSnippetIsAddedToSnippetList()
    {
        /** @var ProductListingSourceList|\PHPUnit_Framework_MockObject_MockObject $stubProductListingSourceList */
        $stubProductListingSourceList = $this->getMock(ProductListingSourceList::class, [], [], '', false);

        $result = $this->snippetRenderer->render($stubProductListingSourceList, $this->stubContextSource);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnly(Snippet::class, $result);
    }
}
