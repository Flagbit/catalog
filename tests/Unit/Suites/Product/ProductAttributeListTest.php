<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\ConflictingContextDataForProductAttributeListException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeNotFoundException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductAttributeListTest extends \PHPUnit_Framework_TestCase
{
    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, new ProductAttributeList());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, new ProductAttributeList());
    }

    public function testItReturnsTheNumberOfAttributes()
    {
        $attributeArray = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar'
        ];
        $this->assertCount(1, ProductAttributeList::fromArray([$attributeArray]));
        $this->assertCount(2, ProductAttributeList::fromArray([$attributeArray, $attributeArray]));
    }

    public function testAnAttributeCanBeRetrievedUsingAString()
    {
        $attribute1 = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar1'
        ];
        $attribute2 = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar2'
        ];

        $attributeList = new ProductAttributeList(
            ProductAttribute::fromArray($attribute1),
            ProductAttribute::fromArray($attribute2)
        );

        $attributesWithCode = $attributeList->getAttributesWithCode('foo');
        $this->assertEquals('bar1', $attributesWithCode[0]->getValue());
        $this->assertEquals('bar2', $attributesWithCode[1]->getValue());
    }

    public function testAnAttributeCanBeRetrievedUsingAnAttributeCodeInstance()
    {
        $attributeArray = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar'
        ];

        $attributeList = new ProductAttributeList(ProductAttribute::fromArray($attributeArray));

        $attributesWithCode = $attributeList->getAttributesWithCode(AttributeCode::fromString('foo'));
        $this->assertEquals('bar', $attributesWithCode[0]->getValue());
    }

    public function testExceptionIsThrownIfNoAttributeWithGivenCodeIsSet()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        (new ProductAttributeList())->getAttributesWithCode('foo');
    }

    public function testAttributeListIsCreatedFromAttributesArray()
    {
        $attributeArray = [
            [
                ProductAttribute::CODE => 'foo',
                ProductAttribute::CONTEXT_DATA => [],
                ProductAttribute::VALUE => 'bar'
            ]
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $attributesWithCode = $attributeList->getAttributesWithCode('foo');
        $attributeWithCode = $attributesWithCode[0];

        $this->assertEquals('bar', $attributeWithCode->getValue());
    }

    public function testItMayContainMultipleProductAttributesWithTheSameCode()
    {
        $attributeArray = [
            [ProductAttribute::CODE => 'foo', ProductAttribute::CONTEXT_DATA => [], ProductAttribute::VALUE => 'bar'],
            [ProductAttribute::CODE => 'foo', ProductAttribute::CONTEXT_DATA => [], ProductAttribute::VALUE => 'baz'],
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $result = $attributeList->getAttributesWithCode('foo');

        $this->assertCount(count($attributeArray), $result);
        $this->assertContainsOnly(ProductAttribute::class, $result);
    }

    /**
     * @param int $numAttributesToAdd
     * @param string[] $expected
     * @dataProvider numberOfAttributesToAddProvider
     */
    public function testItReturnsTheCodesOfAttributesInTheList($numAttributesToAdd, $expected)
    {
        $attributes = [];
        for ($i = 0; $i < $numAttributesToAdd; $i++) {
            $attributes[] = ProductAttribute::fromArray([
                ProductAttribute::CODE => 'attr_' . ($i + 1),
                ProductAttribute::CONTEXT_DATA => [],
                ProductAttribute::VALUE => 'value'
            ]);
        }
        $attributeCodes = (new ProductAttributeList(...$attributes))->getAttributeCodes();
        $this->assertContainsOnly(AttributeCode::class, $attributeCodes);
        $this->assertSame(count($expected), count($attributeCodes));
        foreach ($expected as $idx => $expectedAttributeCodeString) {
            $expectedAttributeCode = AttributeCode::fromString($expectedAttributeCodeString);
            $this->assertTrue($expectedAttributeCode->isEqualTo($attributeCodes[$idx]));
        }
    }

    /**
     * @return array[]
     */
    public function numberOfAttributesToAddProvider()
    {
        return [
            [0, []],
            [1, ['attr_1']],
            [2, ['attr_1', 'attr_2']],
        ];
    }

    public function testHasAttributeReturnsFalseForAttributesNotInTheList()
    {
        $this->assertFalse((new ProductAttributeList())->hasAttribute('foo'));
    }

    public function testHasAttributeReturnsTrueForAttributesInTheList()
    {
        $attributeArray = [[
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar'
        ]];
        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $this->assertTrue($attributeList->hasAttribute('foo'));
    }

    public function testItCanBeSerializedAndRehydrated()
    {
        $attributesArray = [
            [
                ProductAttribute::CODE => 'foo',
                ProductAttribute::CONTEXT_DATA => [],
                ProductAttribute::VALUE => 'bar'
            ],
            [
                ProductAttribute::CODE => 'bar',
                ProductAttribute::CONTEXT_DATA => [],
                ProductAttribute::VALUE => 'buz'
            ]
        ];
        $sourceAttributeList = ProductAttributeList::fromArray($attributesArray);

        $json = json_encode($sourceAttributeList);

        $rehydratedAttributeList = ProductAttributeList::fromArray(json_decode($json, true));
        $this->assertEquals($sourceAttributeList->getAttributeCodes(), $rehydratedAttributeList->getAttributeCodes());
    }

    public function testItThrowsAnExceptionIfContextWithIncompatibleContextDataAreInjected()
    {
        $expectedMessage = 'Conflicting context "locale" data set values found ' .
            'for attributes to be included in one attribute list: "xx_XX" != "yy_YY"';
        $this->setExpectedException(ConflictingContextDataForProductAttributeListException::class, $expectedMessage);
        $attributesArray = [
            [
                ProductAttribute::CODE => 'test1',
                ProductAttribute::CONTEXT_DATA => ['website' => 'a'],
                ProductAttribute::VALUE => 'test'
            ],
            [
                ProductAttribute::CODE => 'test1',
                ProductAttribute::CONTEXT_DATA => ['website' => 'a', 'locale' => 'xx_XX'],
                ProductAttribute::VALUE => 'test'
            ],
            [
                ProductAttribute::CODE => 'test2',
                ProductAttribute::CONTEXT_DATA => ['website' => 'a', 'locale' => 'yy_YY'],
                ProductAttribute::VALUE => 'test'
            ]
        ];
        ProductAttributeList::fromArray($attributesArray);
    }
}
