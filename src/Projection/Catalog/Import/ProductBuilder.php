<?php
namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;

interface ProductBuilder
{
    /**
     * @param Context $context
     * @return Product
     */
    public function getProductForContext(Context $context);
}