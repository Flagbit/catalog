<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductBuilder;

class UpdateProductCommand implements Command
{
    /**
     * @var Product
     */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductBuilder
     */
    public function getProduct()
    {
        return $this->product;
    }
}
