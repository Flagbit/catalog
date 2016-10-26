<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\Price;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices
 * @uses   \LizardsAndPumpkins\Import\Price\Price
 */
class EnrichProductJsonWithPricesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var EnrichProductJsonWithPrices
     */
    private $enrichProductJsonWithPrices;

    /**
     * @param mixed $attributeCode
     * @param mixed $expectedValue
     * @param string[] $attributeData
     */
    private function assertProductJsonDataHas($attributeCode, $expectedValue, array $attributeData)
    {
        $this->assertArrayHasKey($attributeCode, $attributeData);
        $this->assertSame($expectedValue, $attributeData[$attributeCode]);
    }

    private function getPriceAsFractionUnits(string $amount) : int
    {
        return Price::fromDecimalValue($amount)->getAmount();
    }

    protected function setUp()
    {
        $this->stubContext = $this->createMock(Context::class);
        $this->enrichProductJsonWithPrices = new EnrichProductJsonWithPrices($this->stubContext);
    }

    public function testItEnrichesProductDataWithPriceAndSpecialPriceInformation()
    {
        $productData = [];
        $price = $this->getPriceAsFractionUnits('19.99');
        $specialPrice = $this->getPriceAsFractionUnits('17.99');
        
        $this->stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData($productData, $price, $specialPrice);

        $this->assertProductJsonDataHas('price', '19,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_price', 1999, $result['attributes']);
        $this->assertProductJsonDataHas('special_price', '17,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_special_price', 1799, $result['attributes']);
        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }

    public function testItDoesNotAddSpecialPriceDataIfTheSpecialPriceIsNull()
    {
        $productData = [];
        $price = '1999';
        $specialPrice = null;
        
        $this->stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData($productData, $price, $specialPrice);
        
        $this->assertArrayNotHasKey('special_price', $result['attributes']);
        $this->assertArrayNotHasKey('raw_special_price', $result['attributes']);
    }

    public function testItAddsCurrencyInformationToTheProductAttributes()
    {
        $productData = [];
        $price = '1999';
        $specialPrice = '1799';
        $this->stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData($productData, $price, $specialPrice);

        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }
}
