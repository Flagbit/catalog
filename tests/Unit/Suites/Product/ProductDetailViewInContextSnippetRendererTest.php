<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\Snippet;
use Brera\SnippetList;
use Brera\TestFileFixtureTrait;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \Brera\Snippet
 * @uses   \Brera\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailViewInContextSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var ProductDetailViewInContextSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ProductDetailViewBlockRenderer||\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductDetailViewBlockRenderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var UrlPathKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlPathKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->stubProductDetailViewBlockRenderer = $this->getMock(
            ProductDetailViewBlockRenderer::class,
            [],
            [],
            '',
            false
        );
        $this->stubProductDetailViewBlockRenderer->method('render')
            ->willReturn('dummy content');
        $this->stubProductDetailViewBlockRenderer->method('getRootSnippetCode')
            ->willReturn('dummy root block code');
        $this->stubProductDetailViewBlockRenderer->method('getNestedSnippetCodes')
            ->willReturn([]);
        $this->mockSnippetKeyGenerator = $this->getMock(ProductSnippetKeyGenerator::class, [], [], '', false);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn('stub-content-key');
        $this->stubUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class);
        $this->stubUrlPathKeyGenerator->method('getUrlKeyForPathInContext')
            ->willReturn('stub-url-key');
        $this->renderer = new ProductDetailViewInContextSnippetRenderer(
            $this->mockSnippetList,
            $this->stubProductDetailViewBlockRenderer,
            $this->mockSnippetKeyGenerator,
            $this->stubUrlPathKeyGenerator
        );
    }

    public function testProductDetailViewSnippetsAreRendered()
    {
        $this->mockSnippetList->expects($this->exactly(2))->method('add');
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn(2);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);
    }

    public function testContainedJson()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn(2);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);

        $method = new \ReflectionMethod($this->renderer, 'getProductDetailPageMetaSnippet');
        $method->setAccessible(true);
        /** @var Snippet $result */
        $result = $method->invoke($this->renderer);
        $this->assertInternalType('array', json_decode($result->getContent(), true));
    }

    public function testContextPartsFetchingIsDelegatedToKeyGenerator()
    {
        $testContextParts = ['version', 'website', 'language'];
        $this->mockSnippetKeyGenerator->expects($this->once())->method('getContextPartsUsedForKey')
            ->willReturn($testContextParts);

        $this->assertSame($testContextParts, $this->renderer->getUsedContextParts());
    }
}
