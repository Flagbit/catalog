<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\SnippetList;

class ProductInListingSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_in_listing';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var ProductInListingBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(
        SnippetList $snippetList,
        ProductInListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->snippetList = $snippetList;
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductSource)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be instance of ProductSource.');
        }

        $this->addProductInListingSnippetsToList($projectionSourceData, $contextSource);

        return $this->snippetList;
    }

    private function addProductInListingSnippetsToList(ProductSource $productSource, ContextSource $contextSource)
    {
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $productInContext = $productSource->getProductForContext($context);
            $this->addProductInListingInContextSnippetsToList($productInContext, $context);
        }
    }

    private function addProductInListingInContextSnippetsToList(Product $product, Context $context)
    {
        $content = $this->blockRenderer->render($product, $context);
        $key = $this->snippetKeyGenerator->getKeyForContext($context, ['product_id' => $product->getId()]);
        $contentSnippet = Snippet::create($key, $content);
        $this->snippetList->add($contentSnippet);
    }
}