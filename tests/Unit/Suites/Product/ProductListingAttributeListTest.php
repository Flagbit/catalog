<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidProductListingAttributeCodeException;
use LizardsAndPumpkins\Product\Exception\InvalidProductListingAttributeValueException;
use LizardsAndPumpkins\Product\Exception\ProductListingAttributeNotFountException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingAttributeList
 */
class ProductListingAttributeListTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfAttributeCodeIsNotAString()
    {
        $this->setExpectedException(
            InvalidProductListingAttributeCodeException::class,
            'Product listing attribute code must be a string, got "integer".'
        );

        $attributeCode = 0;
        $attributeValue = 'foo';

        ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);
    }

    public function testExceptionIsThrownIfAttributeCodeIsAnEmptyString()
    {
        $this->setExpectedException(
            InvalidProductListingAttributeCodeException::class,
            'Product listing attribute code can not be empty string.'
        );

        $attributeCode = '';
        $attributeValue = 'foo';

        ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);
    }

    public function testExceptionIsThrownIfAttributeValueIsNotScalar()
    {
        $this->setExpectedException(InvalidProductListingAttributeValueException::class);

        $attributeCode = 'foo';
        $nonScalarAttributeValue = [];

        ProductListingAttributeList::fromArray([$attributeCode => $nonScalarAttributeValue]);
    }

    public function testFalseIsReturnedIfRequestedAttributeCodeIsAbsentInTheList()
    {
        $productListingAttributeList = ProductListingAttributeList::fromArray([]);
        $this->assertFalse($productListingAttributeList->hasAttribute('foo'));
    }

    public function testTrueIsReturnedIfListContainsAttributeWithARequestedCode()
    {
        $attributeCode = 'foo';
        $attributeValue = 'bar';
        $productListingAttributeList = ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);

        $this->assertTrue($productListingAttributeList->hasAttribute($attributeCode));
    }

    public function testExceptionIsThrownDuringAttemptToRetrieveAttributeWhichIsAbsentInTheList()
    {
        $this->setExpectedException(ProductListingAttributeNotFountException::class);
        $productListingAttributeList = ProductListingAttributeList::fromArray([]);
        $productListingAttributeList->getAttributeValueByCode('foo');
    }

    public function testAttributeIsReturnedByGivenCode()
    {
        $attributeCode = 'foo';
        $attributeValue = 'bar';
        $productListingAttributeList = ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);

        $this->assertSame($attributeValue, $productListingAttributeList->getAttributeValueByCode($attributeCode));
    }
}
