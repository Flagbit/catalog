<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductAttributeList
 * @uses   \Brera\Product\ProductAttribute
 */
class ProductAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    protected function setUp()
    {
        $this->attributeList = new ProductAttributeList;
    }

    public function testAttributeIsAddedAndRetrievedFromProductAttributeList()
    {
        $attributeArray = [
            'nodeName'      => 'foo',
            'attributes'    => [],
            'value'         => 'bar'
        ];

        $attribute = ProductAttribute::fromArray($attributeArray);

        $this->attributeList->add($attribute);
        $attributesWithCode = $this->attributeList->getAttributesWithCode('foo');
        $result = $attributesWithCode[0];

        $this->assertEquals('bar', $result->getValue());
    }

    public function testExceptionIsThrownIfBlankCodeIsProvided()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        $this->attributeList->getAttributesWithCode('');
    }

    public function testExceptionIsThrownIfNoAttributeWithGivenCodeIsSet()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        $this->attributeList->getAttributesWithCode('foo');
    }

    public function testAttributeListIsCreatedFromAttributesArray()
    {
        $attributeArray = [[
            'nodeName'      => 'foo',
            'attributes'    => [],
            'value'         => 'bar'
        ]];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $attributesWithCode = $attributeList->getAttributesWithCode('foo');
        $result = $attributesWithCode[0];

        $this->assertEquals('bar', $result->getValue());
    }

    public function testAttributeListContainsMultipleAttributeValues()
    {
        $attributeArray = [
            ['nodeName' => 'foo', 'attributes' => [], 'value' => 'bar'],
            ['nodeName' => 'foo', 'attributes' => [], 'value' => 'baz'],
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $result = $attributeList->getAttributesWithCode('foo');

        $this->assertCount(count($attributeArray), $result);
        $this->assertContainsOnly(ProductAttribute::class, $result);
    }

    /**
     * @dataProvider extractAttributesDataProvider
     * @param string $websiteCodeA
     * @param string $websiteCodeB
     * @param string $websiteCodeC
     * @param string $langA
     * @param string $langB
     * @param string $langC
     * @param string $valueA
     * @param string $valueB
     * @param string $valueC
     * @param string[] $contextReturnValueMap
     * @param string $expected
     */
    public function testAttributeValuesForAGivenContextAreExtracted(
        $websiteCodeA,
        $websiteCodeB,
        $websiteCodeC,
        $langA,
        $langB,
        $langC,
        $valueA,
        $valueB,
        $valueC,
        $contextReturnValueMap,
        $expected
    ) {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getStubContextWithReturnValueMap($contextReturnValueMap);
        $attributeCode = 'name';
        $attributesArray = [
            [
                'nodeName'      => $attributeCode,
                'attributes'    => ['website' => $websiteCodeA, 'locale' => $langA],
                'value'         => $valueA
            ],
            [
                'nodeName'      => $attributeCode,
                'attributes'    => ['website' => $websiteCodeB, 'locale' => $langB],
                'value'         => $valueB
            ],
            [
                'nodeName'      => $attributeCode,
                'attributes'    => ['website' => $websiteCodeC, 'locale' => $langC],
                'value'         => $valueC
            ],
        ];
        $attributeList = ProductAttributeList::fromArray($attributesArray);
        $attributeListForContext = $attributeList->getAttributesForContext($stubContext);
        $attributesWithCode = $attributeListForContext->getAttributesWithCode($attributeCode);
        $result = $attributesWithCode[0];

        $this->assertEquals($expected, $result->getValue());
    }

    /**
     * @return array[]
     */
    public function extractAttributesDataProvider()
    {
        return [
            'only-web-in-context' => [
                'webA', 'webB', 'webC', // website codes
                'lang', 'lang', 'lang', // locale codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webB']], // return value map
                'BBB' // expected value
            ],
            'one-match' => [
                'webA', 'webA', 'webB', // website codes
                'langA', 'langB', 'langA', // locale codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webB'], ['locale', 'langA']], // return value map
                'CCC' // expected value
            ],
            'two-match-pick-first' => [
                'webA', 'webB', 'webC', // website codes
                'langB', 'langA', 'langC', // locale codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webA'], ['locale', 'langA']], // return value map
                'AAA' // expected value
            ],
            '3-match-pick-highest' => [
                'webA', 'webB', 'webA', // website codes
                'langB', 'langA', 'langA', // locale codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webA'], ['locale', 'langA']], // return value map
                'CCC' // expected value
            ],
        ];
    }

    public function testExceptionIsThrownWhileCombiningAttributesWithSameCodeButDifferentContextPartsIntoList()
    {
        $attributeA = ProductAttribute::fromArray([
            'nodeName'   => 'attributeCode',
            'attributes' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value'      => 'valueA'
        ]);
        $attributeB = ProductAttribute::fromArray([
            'nodeName'   => 'attributeCode',
            'attributes' => [
                'foo' => 'bar',
            ],
            'value'      => 'valueB'
        ]);

        $this->setExpectedException(AttributeContextPartsMismatchException::class);

        $this->attributeList->add($attributeA);
        $this->attributeList->add($attributeB);
    }

    /**
     * @param mixed[] $returnValueMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubContextWithReturnValueMap(array $returnValueMap)
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getSupportedCodes')
            ->willReturn(array_column($returnValueMap, 0));
        $stubContext->method('getValue')
            ->willReturnMap($returnValueMap);
        return $stubContext;
    }
}
