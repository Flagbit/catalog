<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Exception\InvalidProductTypeIdentifierException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductTypeCode
 */
class ProductTypeCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIfTheTypeIsNotAString()
    {
        $this->setExpectedException(
            InvalidProductTypeIdentifierException::class,
            'The product type identifier has to be a string, got "integer"'
        );
        ProductTypeCode::fromString(123);
    }

    public function testItThrowsAnExceptionIfTheTypeStringIsEmpty()
    {
        $this->setExpectedException(
            InvalidProductTypeIdentifierException::class,
            'The product type identifier can not be empty'
        );
        ProductTypeCode::fromString('');
    }

    public function testItTrimsWhitespaceWhenCheckingIfEmpty()
    {
        $this->setExpectedException(
            InvalidProductTypeIdentifierException::class,
            'The product type identifier can not be empty'
        );
        ProductTypeCode::fromString(' ');
    }

    public function testItReturnsAProductTypeIdentifierInstance()
    {
        $this->assertInstanceOf(ProductTypeCode::class, ProductTypeCode::fromString(SimpleProduct::TYPE_CODE));
    }

    /**
     * @param string $typeString
     * @dataProvider validProductTypeStringProvider
     */
    public function testItReturnsTheTypeStringWhenCastToString($typeString)
    {
        $this->assertSame($typeString, (string)ProductTypeCode::fromString($typeString));
    }

    /**
     * @return array[]
     */
    public function validProductTypeStringProvider()
    {
        return [[SimpleProduct::TYPE_CODE], [ConfigurableProduct::TYPE_CODE], ['test']];
    }
}
