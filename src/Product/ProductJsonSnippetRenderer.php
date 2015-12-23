<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;

class ProductJsonSnippetRenderer
{
    const CODE = 'product_json';

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonKeyGenerator;

    /**
     * @var InternalToPublicProductJsonData
     */
    private $internalToPublicProductJsonData;

    public function __construct(
        SnippetKeyGenerator $productJsonKeyGenerator,
        InternalToPublicProductJsonData $internalToPublicProductJsonData
    ) {
        $this->productJsonKeyGenerator = $productJsonKeyGenerator;
        $this->internalToPublicProductJsonData = $internalToPublicProductJsonData;
    }

    /**
     * @param ProductView $product
     * @return SnippetList
     */
    public function render(ProductView $product)
    {
        $snippetList = new SnippetList();
        $snippetList->add($this->createProductJsonSnippet($product));
        return $snippetList;
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
        $snippet = Snippet::create($key, $this->getJson($product));
        return $snippet;
    }

    /**
     * @param ProductView $product
     * @return string
     */
    private function getJson(ProductView $product)
    {
        $internalJsonData = json_decode(json_encode($product), true);
        $publicJsonData = $this->internalToPublicProductJsonData->transformProduct($internalJsonData);
        return json_encode($publicJsonData);
    }
}
