<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Import\Product\ProductBuilder;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList
 */
class AssociatedProductListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AssociatedProductListBuilder
     */
    private $builder;

    /**
     * @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductBuilder;

    protected function setUp()
    {
        $stubProduct = $this->createMock(ProductDTO::class);
        $stubProduct->method('getId')->willReturnCallback(function () {
            return uniqid();
        });
        $this->stubProductBuilder = $this->createMock(ProductBuilder::class);
        $this->stubProductBuilder->method('getProductForContext')->willReturn($stubProduct);
        $this->stubProductBuilder->method('isAvailableForContext')->willReturn(true);
        
        $this->builder = new AssociatedProductListBuilder(
            $this->stubProductBuilder,
            $this->stubProductBuilder
        );
    }
    
    public function testItReturnsAnAssociatedProductList()
    {
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('__toString')->willReturn('test');
        
        $associatedProductList = $this->builder->getAssociatedProductListForContext($stubContext);
        
        $this->assertInstanceOf(AssociatedProductList::class, $associatedProductList);
        $this->assertCount(2, $associatedProductList);
    }
}
