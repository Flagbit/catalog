<?php

namespace LizardsAndPumpkins\Product\Tax\TaxRates;

use LizardsAndPumpkins\Product\Price;
use LizardsAndPumpkins\Product\Tax\TaxService;

abstract class TwentyOneRunTaxRate implements TaxService
{
    /**
     * @param int|string $rate
     * @return TwentyOneRunGenericTaxRateService
     */
    public static function create($rate)
    {
        return TwentyOneRunGenericTaxRateService::fromInt($rate);
    }
    
    /**
     * @return float
     */
    abstract protected function getFactor();

    /**
     * @return int
     */
    public function getRate()
    {
        return (int) ($this->getFactor() * 100 - 100);
    }

    /**
     * @param Price $price
     * @return Price
     */
    public function applyTo(Price $price)
    {
        $result = round($price->getAmount() * $this->getFactor(), 0, PHP_ROUND_HALF_DOWN);
        return new Price((int) $result);
    }
}
