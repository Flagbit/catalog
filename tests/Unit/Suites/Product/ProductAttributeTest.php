<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductAttribute
 * @uses   \Brera\Product\ProductAttributeList
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testTrueIsReturnedIfAttributeWithGivenCodeExists()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertTrue($attribute->isCodeEqualsTo('foo'));
    }

    public function testFalseIsReturnedIfAttributeWithGivenCodeDoesNotExist()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertFalse($attribute->isCodeEqualsTo('baz'));
    }

    public function testAttributeCodeIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertEquals('foo', $attribute->getCode());
    }

    public function testAttributeValueIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertEquals('bar', $attribute->getValue());
    }

    public function testAttributeWithSubAttributeIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => [
                [
                    'nodeName'   => 'bar',
                    'attributes' => [],
                    'value'      => 1
                ],
                [
                    'nodeName'   => 'baz',
                    'attributes' => [],
                    'value'      => 2
                ]
            ]
        ]);

        $attributeValue = $attribute->getValue();

        $this->assertInstanceOf(ProductAttributeList::class, $attributeValue);
        $this->assertEquals(1, $attributeValue->getAttributesWithCode('bar')[0]->getValue());
        $this->assertEquals(2, $attributeValue->getAttributesWithCode('baz')[0]->getValue());
    }

    public function testIntegerIsReturnedForMatchScore()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getContextMockWithReturnValueMap([]);
        $attribute = $this->createProductAttributeWithArray([]);

        $this->assertInternalType('int', $attribute->getMatchScoreForContext($stubContext));
    }

    public function testOneIsReturnedForMatchScoreForContextWithOneMatch()
    {
        $testWebsiteCode = 'foo';
        $attribute = $this->createProductAttributeWithArray(['website' => $testWebsiteCode, 'locale' => 'bar']);
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getContextMockWithReturnValueMap([
            ['website', $testWebsiteCode],
            ['version', '1'],
        ]);

        $this->assertSame(1, $attribute->getMatchScoreForContext($stubContext));
    }

    public function testTwoIsReturnedForMatchScoreForContextWithTwoMatches()
    {
        $testWebsiteCode = 'foo';
        $testLocaleCode = 'bar';
        $attribute = $this->createProductAttributeWithArray([
            'website'  => $testWebsiteCode,
            'locale' => $testLocaleCode
        ]);
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getContextMockWithReturnValueMap([
            ['website', $testWebsiteCode],
            ['locale', $testLocaleCode],
            ['version', '1'],
        ]);

        $this->assertSame(2, $attribute->getMatchScoreForContext($stubContext));
    }

    public function testZeroIsReturnedForMatchScoreForContextWithNoMatches()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getContextMockWithReturnValueMap([
            ['website', 'buz'],
            ['locale', 'qux'],
            ['version', '1'],
        ]);
        $attribute = $this->createProductAttributeWithArray(['website' => 'foo', 'locale' => 'bar']);

        $this->assertSame(0, $attribute->getMatchScoreForContext($stubContext));
    }

    public function testOneIsReturnedForMatchScoreForContextWithOneMatchAndOneMiss()
    {
        $testLocaleCode = 'bar';
        $attribute = $this->createProductAttributeWithArray(['website' => 'foo', 'locale' => $testLocaleCode]);
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getContextMockWithReturnValueMap([
            ['website', 'buz'],
            ['locale', $testLocaleCode],
            ['version', '1'],
        ]);
        $this->assertSame(1, $attribute->getMatchScoreForContext($stubContext));
    }

    public function testContextPartsOfAttributeAreReturned()
    {
        $contextData = ['foo' => 'bar', 'baz' => 'qux'];

        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'attributeANodeName',
            'attributes' => $contextData,
            'value'      => 'attributeAValue'
        ]);

        $this->assertSame(array_keys($contextData), $attribute->getContextParts());
    }

    public function testFalseIsReturnedIfContentPartsOfAttributesAreDifferent()
    {
        $attributeA = ProductAttribute::fromArray([
            'nodeName'   => 'attributeANodeName',
            'attributes' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value'      => 'attributeAValue'
        ]);
        $attributeB = ProductAttribute::fromArray([
            'nodeName'   => 'attributeBNodeName',
            'attributes' => [
                'foo' => 'bar',
            ],
            'value'      => 'attributeBValue'
        ]);

        $this->assertFalse($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testTrueIsReturnedIfContentPartsOfAttributesAreIdentical()
    {
        $attributeA = ProductAttribute::fromArray([
            'nodeName'   => 'attributeANodeName',
            'attributes' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value'      => 'attributeAValue'
        ]);
        $attributeB = ProductAttribute::fromArray([
            'nodeName'   => 'attributeBNodeName',
            'attributes' => [
                'foo' => 'qux',
                'baz' => 'bar'
            ],
            'value'      => 'attributeBValue'
        ]);

        $this->assertTrue($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testFalseIsReturnedIfAttributeCodesAreDifferent()
    {
        $attributeA = ProductAttribute::fromArray(['nodeName' => 'codeA', 'attributes' => [], 'value' => 'valueA']);
        $attributeB = ProductAttribute::fromArray(['nodeName' => 'codeB', 'attributes' => [], 'value' => 'valueB']);

        $this->assertFalse($attributeA->hasSameCodeAs($attributeB));
    }

    public function testTrueIsReturnedIfAttributeCodesAreIdentical()
    {
        $attributeA = ProductAttribute::fromArray(['nodeName' => 'codeA', 'attributes' => [], 'value' => 'valueA']);
        $attributeB = ProductAttribute::fromArray(['nodeName' => 'codeA', 'attributes' => [], 'value' => 'valueB']);

        $this->assertTrue($attributeA->hasSameCodeAs($attributeB));
    }

    /**
     * @param string[] $attributeContext
     * @return ProductAttribute
     */
    private function createProductAttributeWithArray(array $attributeContext)
    {
        return ProductAttribute::fromArray([
            'nodeName'   => 'name',
            'attributes' => $attributeContext,
            'value'      => 'dummy-test-value'
        ]);
    }

    /**
     * @param string[] $returnValueMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getContextMockWithReturnValueMap(array $returnValueMap)
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getSupportedCodes')->willReturn(array_column($returnValueMap, 0));
        $stubContext->method('getValue')->willReturnMap($returnValueMap);

        return $stubContext;
    }
}
