<?php

namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\Context\Context;

class PricesJsonSnippetTransformation implements SnippetTransformation
{
    /**
     * @var SnippetTransformation
     */
    private $priceSnippetTransformation;

    public function __construct(SnippetTransformation $priceSnippetTransformation)
    {
        $this->priceSnippetTransformation = $priceSnippetTransformation;
    }

    /**
     * @param string $input
     * @param Context $context
     * @return string
     */
    public function __invoke($input, Context $context)
    {
        if (!is_string($input)) {
            return '';
        }

        $allPrices = json_decode($input);
        if (!is_array($allPrices)) {
            return '';
        }

        return json_encode(array_map(function (array $prices) use ($context) {
            return array_map(function ($price) use ($context) {
                return call_user_func($this->priceSnippetTransformation, $price, $context);
            }, $prices);
        }, $allPrices));
    }
}
