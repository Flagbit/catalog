<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder;
use LizardsAndPumpkins\Import\XPathParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToAssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 */
class ConfigurableProductXmlToAssociatedProductListBuilderTest extends TestCase
{
    private $testXml = '
<product type="configurable" sku="config">
    <associated_products>
        <product type="simple" sku="test">
        </product>
    </associated_products>
</product>
    ';

    public function testItReturnsAnAssociatedProductListBuilderInstance()
    {
        $stubXmlToProductTypeBuilderLocator = $this->createMock(ProductXmlToProductBuilderLocator::class);
        $stubXmlToProductTypeBuilderLocator->method('createProductBuilderFromXml')
            ->willReturn($this->createMock(ProductBuilder::class));
        $converter = new ConfigurableProductXmlToAssociatedProductListBuilder($stubXmlToProductTypeBuilderLocator);

        $result = $converter->createAssociatedProductListBuilder(new XPathParser($this->testXml));
        
        $this->assertInstanceOf(AssociatedProductListBuilder::class, $result);
    }
}
