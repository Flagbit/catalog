<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\ProductAttributeDoesNotContainContextPartException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testTrueIsReturnedIfAttributeHasGivenCode()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => ProductAttribute::VALUE
        ]);

        $this->assertTrue($attribute->isCodeEqualsTo('foo'));
    }

    public function testFalseIsReturnedIfAttributeHasDifferentCode()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => ProductAttribute::VALUE
        ]);

        $this->assertFalse($attribute->isCodeEqualsTo('bar'));
    }

    public function testAttributeCodeIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar'
        ]);

        $this->assertEquals('foo', $attribute->getCode());
    }

    public function testAttributeValueIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar'
        ]);

        $this->assertEquals('bar', $attribute->getValue());
    }

    public function testAttributeWithSubAttributeIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => [
                [
                    ProductAttribute::CODE => 'bar',
                    ProductAttribute::CONTEXT_DATA => [],
                    ProductAttribute::VALUE => 1
                ],
                [
                    ProductAttribute::CODE => 'baz',
                    ProductAttribute::CONTEXT_DATA => [],
                    ProductAttribute::VALUE => 2
                ]
            ]
        ]);

        $attributeValue = $attribute->getValue();

        $this->assertInstanceOf(ProductAttributeList::class, $attributeValue);
        $this->assertEquals(1, $attributeValue->getAttributesWithCode('bar')[0]->getValue());
        $this->assertEquals(2, $attributeValue->getAttributesWithCode('baz')[0]->getValue());
    }

    public function testContextPartsOfAttributeAreReturned()
    {
        $contextData = ['foo' => 'bar', 'baz' => 'qux'];

        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attributeACode',
            ProductAttribute::CONTEXT_DATA => $contextData,
            ProductAttribute::VALUE => 'attributeAValue'
        ]);

        $this->assertSame(array_keys($contextData), $attribute->getContextParts());
    }

    public function testExceptionIsThrownIfRequestedContextPartIsNotPresent()
    {
        $this->setExpectedException(
            ProductAttributeDoesNotContainContextPartException::class,
            'The context part "foo" is not present on the attribute "attributeCode"'
        );
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attributeCode',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'attributeValue'
        ]);
        $attribute->getContextPartValue('foo');
    }

    public function testItReturnsTheContextPartIfItIsPresent()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attributeCode',
            ProductAttribute::CONTEXT_DATA => ['foo' => 'bar'],
            ProductAttribute::VALUE => 'attributeValue'
        ]);
        $this->assertSame('bar', $attribute->getContextPartValue('foo'));
    }

    public function testFalseIsReturnedIfContentPartsOfAttributesAreDifferent()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attributeACode',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            ProductAttribute::VALUE => 'attributeAValue'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attributeBCode',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'bar',
            ],
            ProductAttribute::VALUE => 'attributeBValue'
        ]);

        $this->assertFalse($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testTrueIsReturnedIfContentPartsOfAttributesAreIdentical()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attributeACode',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            ProductAttribute::VALUE => 'attributeAValue'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attributeBCode',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'qux',
                'baz' => 'bar'
            ],
            ProductAttribute::VALUE => 'attributeBValue'
        ]);

        $this->assertTrue($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testFalseIsReturnedIfAttributeCodesAreDifferent()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'codeA',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueA'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'codeB',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueB'
        ]);

        $this->assertFalse($attributeA->hasSameCodeAs($attributeB));
    }

    public function testTrueIsReturnedIfAttributeCodesAreIdentical()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'codeA',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueA'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'codeA',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueB'
        ]);

        $this->assertTrue($attributeA->hasSameCodeAs($attributeB));
    }

    public function testItReturnsTheContextDataSet()
    {
        $contextDataSet = [
            'foo' => 'bar',
            'buz' => 'qux'
        ];
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'test',
            ProductAttribute::CONTEXT_DATA => $contextDataSet,
            ProductAttribute::VALUE => 'abc'
        ]);
        $this->assertSame($contextDataSet, $attribute->getContextDataSet());
    }
}
