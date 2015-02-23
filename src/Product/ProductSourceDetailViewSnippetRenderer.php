<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\SnippetRenderer;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;

class ProductSourceDetailViewSnippetRenderer implements SnippetRenderer
{
    /**
     * @var ProductSource
     */
    private $productSource;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var ProductInContextDetailViewSnippetRenderer
     */
    private $productInContextRenderer;

    /**
     * @param SnippetResultList $snippetResultList
     * @param ProductInContextDetailViewSnippetRenderer $productInContextRenderer
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        ProductInContextDetailViewSnippetRenderer $productInContextRenderer
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->productInContextRenderer = $productInContextRenderer;
    }

    /**
     * @param ProjectionSourceData|ProductSource $productSource
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->validateProjectionSourceData($productSource);
        $this->initProperties($productSource, $contextSource);
        
        $this->createProductDetailViewSnippets();

        return $this->snippetResultList;
    }

    private function createProductDetailViewSnippets()
    {
        foreach ($this->contextSource->extractContexts($this->getContextParts()) as $context) {
            $productInContext = $this->productSource->getProductForContext($context);
            $inContextSnippetResultList = $this->productInContextRenderer->render($productInContext, $context);
            $this->snippetResultList->merge($inContextSnippetResultList);
        }
    }

    /**
     * @return string[]
     */
    private function getContextParts()
    {
        return ['version', 'website', 'language'];
    }

    /**
     * @param ProjectionSourceData $productSource
     * @throws InvalidArgumentException
     */
    private function validateProjectionSourceData(ProjectionSourceData $productSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }
    }

    /**
     * @param ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     */
    private function initProperties(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->productSource = $productSource;
        $this->contextSource = $contextSource;
        $this->snippetResultList->clear();
    }
}
