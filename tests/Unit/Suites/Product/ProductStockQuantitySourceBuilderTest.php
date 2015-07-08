<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductStockQuantitySourceBuilder
 * @uses   \Brera\Product\PoCSku
 * @uses   \Brera\Product\ProductStockQuantity
 * @uses   \Brera\Product\ProductStockQuantitySource
 * @uses   \Brera\Utils\XPathParser
 */
class ProductStockQuantitySourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getXmlWithInvalidNumberOfSkuNodes
     * @param string $xml
     */
    public function testExceptionIsThrownIfMoreThanOneSkuNodeIsPresentOnTheRootLevel($xml)
    {
        $this->setExpectedException(
            InvalidProductStockQuantitySourceDataException::class,
            'There must be just one "sku" node in product stock quantity source data.'
        );
        (new ProductStockQuantitySourceBuilder)->createFromXml($xml);
    }

    /**
     * @return array[]
     */
    public function getXmlWithInvalidNumberOfSkuNodes()
    {
        return [
            ['<?xml version="1.0"?><node><quantity/><sku/><sku/></node>'],
            ['<?xml version="1.0"?><node><quantity/></node>']
        ];
    }

    /**
     * @dataProvider getXmlWithInvalidNumberOfQuantityNodes
     * @param string $xml
     */
    public function testExceptionIsThrownIfMoreThanOneQuantityNodeIsPresentOnTheRootLevel($xml)
    {
        $this->setExpectedException(
            InvalidProductStockQuantitySourceDataException::class,
            'There must be just one "quantity" node in product stock quantity source data.'
        );
        (new ProductStockQuantitySourceBuilder)->createFromXml($xml);
    }

    /**
     * @return array[]
     */
    public function getXmlWithInvalidNumberOfQuantityNodes()
    {
        return [
            ['<?xml version="1.0"?><node><sku/><quantity/><quantity/></node>'],
            ['<?xml version="1.0"?><node><sku/></node>']
        ];
    }

    public function testProductStockQuantitySourceIsCreatedFromXml()
    {
        $xml = <<<EOX
<?xml version="1.0"?>
<rootNode website="foo" language="bar">
    <sku>baz</sku>
    <quantity>1</quantity>
</rootNode>
EOX;
        $productStockQuantitySource = (new ProductStockQuantitySourceBuilder)->createFromXml($xml);

        $resultSku = $productStockQuantitySource->getSku();
        $resultQuantity = $productStockQuantitySource->getStock();
        $resultContextData = $productStockQuantitySource->getContextData();

        $this->assertInstanceOf(ProductStockQuantitySource::class, $productStockQuantitySource);
        $this->assertEquals('baz', $resultSku);
        $this->assertSame(1, $resultQuantity->getQuantity());
        $this->assertSame(['website' => 'foo', 'language' => 'bar'], $resultContextData);
    }
}
