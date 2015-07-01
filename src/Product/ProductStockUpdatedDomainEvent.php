<?php

namespace Brera\Product;

use Brera\DomainEvent;

class ProductStockUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var Sku
     */
    private $sku;

    /**
     * @var ProductStockQuantity
     */
    private $stock;

    public function __construct(Sku $sku, ProductStockQuantity $stock)
    {
        $this->sku = $sku;
        $this->stock = $stock;
    }

    /**
     * @return Sku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return ProductStockQuantity
     */
    public function getStock()
    {
        return $this->stock;
    }
}
