<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

class UpdateProductListingCommand implements Command
{
    /**
     * @var ProductListingMetaInfo
     */
    private $productListingMetaInfo;

    public function __construct(ProductListingMetaInfo $productListingMetaInfo)
    {
        $this->productListingMetaInfo = $productListingMetaInfo;
    }

    /**
     * @return ProductListingMetaInfo
     */
    public function getProductListingMetaInfo()
    {
        return $this->productListingMetaInfo;
    }
}
