<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductInListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var ProductInListingSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @param string $dummyProductIdString
     * @return ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProductView($dummyProductIdString)
    {
        $stubProductId = $this->createMock(ProductId::class);
        $stubProductId->method('__toString')->willReturn($dummyProductIdString);

        /** @var ProductView|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(ProductView::class);
        $stubProduct->method('getId')->willReturn($stubProductId);
        $stubProduct->method('getContext')->willReturn($this->createMock(Context::class));
        $stubProduct->method('jsonSerialize')->willReturn(['product_id' => $stubProductId]);
        
        return $stubProduct;
    }

    /**
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @return ProductInListingSnippetRenderer
     */
    private function createInstanceUnderTest($snippetKeyGenerator)
    {
        return new ProductInListingSnippetRenderer($snippetKeyGenerator);
    }

    protected function setUp()
    {
        $this->mockSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->snippetRenderer = $this->createInstanceUnderTest($this->mockSnippetKeyGenerator);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotAProductBuilder()
    {
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->snippetRenderer->render('invalid-projection-source-data');
    }

    public function testProductInListingViewSnippetIsRendered()
    {
        $dummyProductId = 'foo';
        $stubProduct = $this->getStubProductView($dummyProductId);

        $result = $this->snippetRenderer->render($stubProduct);

        $this->assertCount(1, $result);
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $dummyProductId = 'foo';
        $stubProduct = $this->getStubProductView($dummyProductId);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGenerator */
        $mockSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), [ProductDTO::ID => $stubProduct->getId()])
            ->willReturn('stub-content-key');

        $snippetRenderer = $this->createInstanceUnderTest($mockSnippetKeyGenerator);

        $snippetRenderer->render($stubProduct);
    }
}
