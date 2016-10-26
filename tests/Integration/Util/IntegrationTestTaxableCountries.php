<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Import\Tax\TaxableCountries;

class IntegrationTestTaxableCountries implements TaxableCountries
{
    private static $countries = ['DE', 'FR'];

    public function getIterator() : \Iterator
    {
        return new \ArrayIterator(self::$countries);
    }

    /**
     * @return string[]
     */
    public function getCountries() : array
    {
        return self::$countries;
    }
}
