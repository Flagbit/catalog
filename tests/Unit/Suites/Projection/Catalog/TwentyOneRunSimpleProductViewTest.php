<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunSimpleProductView
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class TwentyOneRunSimpleProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    /**
     * @var TwentyOneRunSimpleProductView
     */
    private $productView;

    /**
     * @param $attributeCodeString
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubAttributeCode($attributeCodeString)
    {
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($attributeCodeString);

        return $stubAttributeCode;
    }

    /**
     * @param string $attributeCodeString
     * @return ProductAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubAttributeWithCode($attributeCodeString)
    {
        $stubAttributeCode = $this->getStubAttributeCode($attributeCodeString);

        $stubProductAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttribute->method('getCode')->willReturn($stubAttributeCode);
        $stubProductAttribute->method('isCodeEqualTo')->willReturnCallback(function ($code) use ($attributeCodeString) {
            return $code === $attributeCodeString;
        });
        $stubProductAttribute->method('getContextParts')->willReturn([]);
        $stubProductAttribute->method('getContextDataSet')->willReturn([]);

        return $stubProductAttribute;
    }

    /**
     * @param string $attributeCodeString
     * @param mixed $attributeValue
     * @return ProductAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubAttributeWithCodeAndValue($attributeCodeString, $attributeValue)
    {
        $stubProductAttribute = $this->createStubAttributeWithCode($attributeCodeString);
        $stubProductAttribute->method('getValue')->willReturn($attributeValue);

        return $stubProductAttribute;
    }

    /**
     * @param ProductAttribute[] ...$stubProductAttributes
     * @return ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductAttributeList(ProductAttribute ...$stubProductAttributes)
    {
        $stubAttributeList = $this->getMock(ProductAttributeList::class, [], [], '', false);
        $stubAttributeList->method('getAllAttributes')->willReturn($stubProductAttributes);

        return $stubAttributeList;
    }

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->productView = new TwentyOneRunSimpleProductView($this->mockProduct);
    }

    public function testOriginalProductIsReturned()
    {
        $this->assertSame($this->mockProduct, $this->productView->getOriginalProduct());
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductView::class, $this->productView);
    }

    public function testGettingProductIdIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getId');
        $this->productView->getId();
    }

    public function testGettingFirstValueOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame($testAttributeValue, $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testGettingFirstValueOfPriceAttributeReturnsEmptyString($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $stubPriceAttribute = $this->createStubAttributeWithCodeAndValue($priceAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubPriceAttribute);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($priceAttributeCode));
    }

    public function priceAttributeCodeProvider()
    {
        return [
            ['price'],
            ['special_price']
        ];
    }

    public function testGettingFirstValueOfBackordersAttributeReturnsEmptyString()
    {
        $testAttributeCode = 'backorders';
        $testAttributeValue = true;

        $stubPriceAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubPriceAttribute);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    public function testGettingAllValuesOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame([$testAttributeValue], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    public function testGettingAllValuesOfPriceAttributeReturnsEmptyArray()
    {
        $testAttributeCode = 'price';
        $testAttributeValue = 1000;

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeValue));
    }

    public function testGettingAllValuesOfSpecialPriceAttributeReturnsEmptyArray()
    {
        $testAttributeCode = 'special_price';
        $testAttributeValue = 1000;

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    public function testGettingAllValuesOfBackordersAttributeReturnsEmptyArray()
    {
        $testAttributeCode = 'backorders';
        $testAttributeValue = true;

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    public function testCheckingIfProductHasAnAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';

        $stubAttribute = $this->createStubAttributeWithCode($testAttributeCode);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertTrue($this->productView->hasAttribute($testAttributeCode));
    }

    public function testProductViewAttributeListDoesNotHavePrice()
    {
        $priceAttributeCodeString = 'price';

        $stubPriceAttribute = $this->createStubAttributeWithCode($priceAttributeCodeString);
        $stubAttributeList = $this->createStubProductAttributeList($stubPriceAttribute);
        $stubAttributeList->method('hasAttribute')->with($priceAttributeCodeString)->willReturn(true);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertFalse($this->productView->hasAttribute($priceAttributeCodeString));
    }

    public function testProductViewAttributeListDoesNotHaveSpecialPrice()
    {
        $specialPriceAttributeCodeString = 'special_price';

        $stubSpecialPriceAttribute = $this->createStubAttributeWithCode($specialPriceAttributeCodeString);
        $stubAttributeList = $this->createStubProductAttributeList($stubSpecialPriceAttribute);
        $stubAttributeList->method('hasAttribute')->with($specialPriceAttributeCodeString)->willReturn(true);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertFalse($this->productView->hasAttribute($specialPriceAttributeCodeString));
    }

    public function testProductViewAttributeListDoesNotHaveBackorders()
    {
        $specialPriceAttributeCodeString = 'backorders';

        $stubSpecialPriceAttribute = $this->createStubAttributeWithCode($specialPriceAttributeCodeString);
        $stubAttributeList = $this->createStubProductAttributeList($stubSpecialPriceAttribute);
        $stubAttributeList->method('hasAttribute')->with($specialPriceAttributeCodeString)->willReturn(true);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertFalse($this->productView->hasAttribute($specialPriceAttributeCodeString));
    }

    public function testFilteredProductAttributeListIsReturned()
    {
        $nonPriceAttribute = $this->createStubAttributeWithCode('foo');
        $priceAttribute = $this->createStubAttributeWithCode('price');
        $specialPriceAttribute = $this->createStubAttributeWithCode('special_price');
        $backordersAttribute = $this->createStubAttributeWithCode('backorders');

        $stubAttributeList = $this->createStubProductAttributeList(
            $nonPriceAttribute,
            $priceAttribute,
            $specialPriceAttribute,
            $backordersAttribute
        );

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $result = $this->productView->getAttributes();

        $this->assertCount(1, $result);
        $this->assertContains($nonPriceAttribute, $result->getAllAttributes());
    }

    public function testProductAttributeListIsMemoized()
    {
        $stubAttributeList = $this->createStubProductAttributeList();
        $this->mockProduct->expects($this->once())->method('getAttributes')->willReturn($stubAttributeList);

        $this->productView->getAttributes();
        $this->productView->getAttributes();
    }

    public function testGettingProductContextIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getContext');
        $this->productView->getContext();
    }

    public function testGettingProductImagesIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getImages');
        $this->productView->getImages();
    }

    public function testGettingProductImageCountIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getImageCount');
        $this->productView->getImageCount();
    }

    public function testGettingProductImageByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->expects($this->once())->method('getImageByNumber')->with($testImageNumber);
        $this->productView->getImageByNumber($testImageNumber);
    }

    public function testGettingProductImageFileNameByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->expects($this->once())->method('getImageFileNameByNumber')->with($testImageNumber);
        $this->productView->getImageFileNameByNumber($testImageNumber);
    }

    public function testGettingProductImageLabelByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->expects($this->once())->method('getImageLabelByNumber')->with($testImageNumber);
        $this->productView->getImageLabelByNumber($testImageNumber);
    }

    public function testGettingProductMainImageFileNameIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getMainImageFileName');
        $this->productView->getMainImageFileName();
    }

    public function testGettingProductMainImageLabelIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getMainImageLabel');
        $this->productView->getMainImageLabel();
    }

    public function testGettingProductTaxClassIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getTaxClass');
        $this->productView->getTaxClass();
    }

    public function testJsonSerializedProductViewHasNoPrices()
    {
        $nonPriceAttribute = $this->createStubAttributeWithCode('foo');
        $priceAttribute = $this->createStubAttributeWithCode('price');
        $specialPriceAttribute = $this->createStubAttributeWithCode('special_price');
        $backordersAttribute = $this->createStubAttributeWithCode('backorders');

        $stubAttributeList = $this->createStubProductAttributeList(
            $nonPriceAttribute,
            $priceAttribute,
            $specialPriceAttribute,
            $backordersAttribute
        );

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);
        $this->mockProduct->method('jsonSerialize')->willReturn([]);

        $result = $this->productView->jsonSerialize();

        /** @var ProductAttributeList $attributesList */
        $attributesList = $result['attributes'];

        $this->assertContains($nonPriceAttribute, $attributesList->getAllAttributes());
    }

    public function testMaximumPurchasableQuantityIsReturnedIfProductIsAvailableForBackorders()
    {
        $stockAttributeCode = 'stock_qty';

        $stockQtyAttribute = $this->createStubAttributeWithCodeAndValue($stockAttributeCode, 1);
        $backordersAttribute = $this->createStubAttributeWithCodeAndValue('backorders', 'true');
        $stubAttributeList = $this->createStubProductAttributeList($stockQtyAttribute, $backordersAttribute);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);
        $this->mockProduct->method('getFirstValueOfAttribute')->with('backorders')->willReturn('true');
        $result = $this->productView->getFirstValueOfAttribute($stockAttributeCode);

        $this->assertSame(TwentyOneRunSimpleProductView::MAX_PURCHASABLE_QTY, $result);
    }

    public function testMaximumPurchasableQuantityIsReturnedIfProductQuantityIsGreaterThanMaximumPurchasableQuantity()
    {
        $stockAttributeCode = 'stock_qty';

        $stockQtyAttribute = $this->createStubAttributeWithCodeAndValue($stockAttributeCode, 6);
        $stubAttributeList = $this->createStubProductAttributeList($stockQtyAttribute);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);
        $result = $this->productView->getFirstValueOfAttribute($stockAttributeCode);

        $this->assertSame(TwentyOneRunSimpleProductView::MAX_PURCHASABLE_QTY, $result);
    }
}
