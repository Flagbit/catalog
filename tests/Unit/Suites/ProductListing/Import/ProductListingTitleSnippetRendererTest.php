<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductListingTitleSnippetRendererTest extends TestCase
{
    private $testSnippetKey = ProductListingTitleSnippetRenderer::CODE . '_foo';

    /**
     * @var ProductListingTitleSnippetRenderer
     */
    private $renderer;

    /**
     * @var ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListing;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingTitleSnippetKeyGenerator;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    protected function setUp()
    {
        $this->stubProductListingTitleSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubProductListingTitleSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testSnippetKey);
        $this->stubContextBuilder = $this->createMock(ContextBuilder::class);
        $this->stubContextBuilder->method('createContext')->willReturn($this->createMock(Context::class));
        $this->renderer = new ProductListingTitleSnippetRenderer(
            $this->stubProductListingTitleSnippetKeyGenerator,
            $this->stubContextBuilder
        );
        $this->stubProductListing = $this->createMock(ProductListing::class);
        $this->stubProductListing->method('getContextData')->willReturn([]);
    }
    
    public function testItImplementsTheSnippetRendererInterface()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testEmptyArrayIsReturnedIfProductListingHasNoTitleAttribute()
    {
        $this->stubProductListing->method('hasAttribute')
            ->with(ProductListingTitleSnippetRenderer::TITLE_ATTRIBUTE_CODE)->willReturn(false);

        $this->assertSame([], $this->renderer->render($this->stubProductListing));
    }

    public function testItReturnsAProductListingTitleSnippet()
    {
        $testTitle = 'foo';

        $this->stubProductListing->method('hasAttribute')
            ->with(ProductListingTitleSnippetRenderer::TITLE_ATTRIBUTE_CODE)->willReturn(true);
        $this->stubProductListing->method('getAttributeValueByCode')
            ->with(ProductListingTitleSnippetRenderer::TITLE_ATTRIBUTE_CODE)->willReturn($testTitle);

        $result = $this->renderer->render($this->stubProductListing);
        
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $result);
        $this->assertSame($this->testSnippetKey, $result[0]->getKey());
        $this->assertSame($testTitle, $result[0]->getContent());
    }
}
