<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class ProductJsonSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_json';

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonKeyGenerator;

    public function __construct(SnippetKeyGenerator $productJsonKeyGenerator)
    {
        $this->productJsonKeyGenerator = $productJsonKeyGenerator;
    }

    /**
     * @param ProductView $product
     * @return Snippet[]
     */
    public function render(ProductView $product)
    {
        return [
            $this->createProductJsonSnippet($product)
        ];
    }

    /**
     * @param ProductView $product
     * @return Snippet
     */
    private function createProductJsonSnippet(ProductView $product)
    {
        $key = $this->productJsonKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        return Snippet::create($key, json_encode($product));
    }
}
