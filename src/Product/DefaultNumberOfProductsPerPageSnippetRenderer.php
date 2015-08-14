<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class DefaultNumberOfProductsPerPageSnippetRenderer implements SnippetRenderer
{
    const CODE = 'default_number_of_products_per_page';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(SnippetList $snippetList, SnippetKeyGenerator $snippetKeyGenerator)
    {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    /**
     * @param ProductListingSourceList $productListingSourceList
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProductListingSourceList $productListingSourceList, ContextSource $contextSource)
    {
        $contextParts = $this->snippetKeyGenerator->getContextPartsUsedForKey();
        $contexts = $contextSource->getContextsForParts($contextParts);
        foreach ($contexts as $context) {
            $this->renderSnippetInContext($productListingSourceList, $context);
        }

        return $this->snippetList;
    }

    private function renderSnippetInContext(ProductListingSourceList $productListingSourceList, Context $context)
    {
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $availableNumbersOfItemsPerPage = $productListingSourceList->getListOfAvailableNumberOfItemsPerPageForContext(
            $context
        );
        $snippetContent = array_shift($availableNumbersOfItemsPerPage);
        $snippet = Snippet::create($snippetKey, $snippetContent);
        $this->snippetList->add($snippet);
    }
}
