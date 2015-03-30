<?php

namespace Brera\Product;

use Brera\ProjectionSourceData;

/* TODO: Does it really have to extend ProjectionSourceData? */
class Product implements ProjectionSourceData
{
    /**
     * @var ProductId
     */
    private $productId;
    
    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    /**
     * @param ProductId $productId
     * @param ProductAttributeList $attributeList
     */
    public function __construct(ProductId $productId, ProductAttributeList $attributeList)
    {
        $this->productId = $productId;
        $this->attributeList = $attributeList;
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->productId;
    }

    /**
     * @param string $attributeCode
     * @return string|ProductAttributeList
     */
    public function getAttributeValue($attributeCode)
    {
        return $this->attributeList->getAttribute($attributeCode)->getValue();
    }
}
