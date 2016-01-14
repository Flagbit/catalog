<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\IntlFormatter;
use SebastianBergmann\Money\Money;

class ProductJsonService
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $priceSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $specialPriceSnippetKeyGenerator;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $productJsonSnippetKeyGenerator,
        SnippetKeyGenerator $priceSnippetKeyGenerator,
        SnippetKeyGenerator $specialPriceSnippetKeyGenerator,
        Context $context
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
        $this->priceSnippetKeyGenerator = $priceSnippetKeyGenerator;
        $this->specialPriceSnippetKeyGenerator = $specialPriceSnippetKeyGenerator;
        $this->context = $context;
    }

    /**
     * @param ProductId[] $productIds
     * @return array[]
     */
    public function get(ProductId ...$productIds)
    {
        return $this->buildProductData(
            $this->getProductJsonSnippetKeys($productIds),
            $this->getPriceSnippetKeys($productIds),
            $this->getSpecialPriceSnippetKeys($productIds)
        );
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getProductJsonSnippetKeys(array $productIds)
    {
        return $this->getSnippetKeys($productIds, $this->productJsonSnippetKeyGenerator);
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getPriceSnippetKeys(array $productIds)
    {
        return $this->getSnippetKeys($productIds, $this->priceSnippetKeyGenerator);
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getSpecialPriceSnippetKeys(array $productIds)
    {
        return $this->getSnippetKeys($productIds, $this->specialPriceSnippetKeyGenerator);
    }

    /**
     * @param ProductId[] $productIds
     * @param SnippetKeyGenerator $keyGenerator
     * @return string[]
     */
    private function getSnippetKeys(array $productIds, SnippetKeyGenerator $keyGenerator)
    {
        return array_map(function (ProductId $productId) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $productId]);
        }, $productIds);
    }

    /**
     * @param string[] $productJsonSnippetKeys
     * @param string[] $priceSnippetKeys
     * @param string[] $specialPriceSnippetKeys
     * @return array[]
     */
    private function buildProductData($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys)
    {
        $snippets = $this->getSnippets($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);

        return array_map(function ($productJsonSnippetKey, $priceKey, $specialPriceKey) use ($snippets) {
            $productData = json_decode($snippets[$productJsonSnippetKey], true);
            return $this->addGivenPricesToProductData(
                $productData,
                $snippets[$priceKey],
                @$snippets[$specialPriceKey],
                $this->getCurrencyCode()
            );
        }, $productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
    }

    /**
     * @param string[] $productJsonSnippetKeys
     * @param string[] $priceSnippetKeys
     * @param string[] $specialPriceSnippetKeys
     * @return string[]
     */
    private function getSnippets($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys)
    {
        $keys = array_merge($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
        return $this->dataPoolReader->getSnippets($keys);
    }

    /**
     * @param string[] $productData
     * @param string $price
     * @param string $specialPrice
     * @param string $currencyCode
     * @return array[]
     */
    public function addGivenPricesToProductData(array $productData, $price, $specialPrice, $currencyCode)
    {
        $currency = new Currency($currencyCode);
        $productData['attributes']['raw_price'] = $price;
        $productData['attributes']['price'] = $this->formatPriceSnippet($price, $currency);
        $productData['attributes']['price_currency'] = $currencyCode;
        $productData['attributes']['price_faction_digits'] = $currency->getDefaultFractionDigits();
        $productData['attributes']['price_base_unit'] = $currency->getSubUnit();

        if (null !== $specialPrice) {
            $productData['attributes']['raw_special_price'] = $specialPrice;
            $productData['attributes']['special_price'] = $this->formatPriceSnippet($specialPrice, $currency);
        }
        
        return $productData;
    }

    /**
     * @param string $price
     * @param string $currency
     * @return string
     */
    private function formatPriceSnippet($price, Currency $currency)
    {
        $locale = $this->getLocaleString($this->context);
        return (new IntlFormatter($locale))->format(new Money((int) $price, $currency));
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getLocaleString(Context $context)
    {
        $locale = $context->getValue(ContextLocale::CODE);
        if (is_null($locale)) {
            throw new NoValidLocaleInContextException('No valid locale in context');
        }
        return $locale;
    }

    /**
     * @return string
     */
    private function getCurrencyCode()
    {
        return 'EUR';
    }
}
