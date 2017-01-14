<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

interface SearchCriteria extends \JsonSerializable
{
    /**
     * @return mixed[]
     */
    public function toArray() : array;
}
