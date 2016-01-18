<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Price;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;
use LizardsAndPumpkins\TaxableCountries;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var string[]
     */
    private $indexAttributeCodes;

    /**
     * @var AttributeValueCollectorLocator
     */
    private $valueCollectorLocator;

    /**
     * @var TaxableCountries
     */
    private $taxableCountries;

    /**
     * @var TaxServiceLocator
     */
    private $taxServiceLocator;

    /**
     * @param string[] $indexAttributeCodes
     * @param AttributeValueCollectorLocator $valueCollector
     * @param TaxableCountries $taxableCountries
     * @param TaxServiceLocator $taxServiceLocator
     */
    public function __construct(
        array $indexAttributeCodes,
        AttributeValueCollectorLocator $valueCollector,
        TaxableCountries $taxableCountries,
        TaxServiceLocator $taxServiceLocator
    ) {
        $this->indexAttributeCodes = $indexAttributeCodes;
        $this->valueCollectorLocator = $valueCollector;
        $this->taxableCountries = $taxableCountries;
        $this->taxServiceLocator = $taxServiceLocator;
    }

    /**
     * @param Product $projectionSourceData
     * @return SearchDocument
     */
    public function aggregate($projectionSourceData)
    {
        if (!($projectionSourceData instanceof Product)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        return $this->createSearchDocument($projectionSourceData);
    }

    /**
     * @param Product $product
     * @return SearchDocument
     */
    private function createSearchDocument(Product $product)
    {
        $fieldsCollection = $this->createSearchDocumentFieldsCollection($product);

        return new SearchDocument($fieldsCollection, $product->getContext(), $product->getId());
    }

    /**
     * @param Product $product
     * @return SearchDocumentFieldCollection
     */
    private function createSearchDocumentFieldsCollection(Product $product)
    {
        $attributesMap = $this->createFieldsForIndexAttributes($product);

        $pricesInclTax = $this->createFieldsForPriceInclTax($product);

        return SearchDocumentFieldCollection::fromArray(
            array_merge($attributesMap, $pricesInclTax, ['product_id' => (string) $product->getId()])
        );
    }

    /**
     * @param Product $product
     * @return array[]
     */
    private function createFieldsForIndexAttributes(Product $product)
    {
        return array_reduce($this->indexAttributeCodes, function ($carry, $attributeCode) use ($product) {
            $codeAndValues = [$attributeCode => $this->getAttributeValuesForSearchDocument($product, $attributeCode)];
            return array_merge($carry, $codeAndValues);
        }, []);
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     * @return array[]
     */
    private function getAttributeValuesForSearchDocument(Product $product, $attributeCode)
    {
        $collector = $this->valueCollectorLocator->forProduct($product);
        return $collector->getValues($product, AttributeCode::fromString($attributeCode));
    }

    /**
     * @param Product $product
     * @return array[]
     */
    private function createFieldsForPriceInclTax(Product $product)
    {
        if (! $product->hasAttribute('price')) {
            return [];
        }

        return array_reduce($this->taxableCountries->getCountries(), function ($carry, $country) use ($product) {
            $priceInclTax = $this->getPriceIncludingTaxForCountry($product, $country);
            $fieldCode = sprintf('price_incl_tax_%s', strtolower($country));
            return array_merge($carry, [$fieldCode => [(string) $priceInclTax]]);
        }, []);
    }

    /**
     * @param Product $product
     * @param string $countryCode
     * @return Price
     */
    private function getPriceIncludingTaxForCountry(Product $product, $countryCode)
    {
        $amountString = (string) $this->getAttributeValuesForSearchDocument($product, 'price')[0];
        $options = $this->createTaxServiceLocatorOptions($product, $countryCode);
        return $this->taxServiceLocator->get($options)->applyTo(Price::fromString($amountString));
    }

    /**
     * @param Product $product
     * @param string $countryCode
     * @return string[]
     */
    private function createTaxServiceLocatorOptions(Product $product, $countryCode)
    {
        $context = $product->getContext();
        return [
            TaxServiceLocator::OPTION_WEBSITE           => $context->getValue(ContextWebsite::CODE),
            TaxServiceLocator::OPTION_PRODUCT_TAX_CLASS => $product->getTaxClass(),
            TaxServiceLocator::OPTION_COUNTRY           => $countryCode,
        ];
    }
}
