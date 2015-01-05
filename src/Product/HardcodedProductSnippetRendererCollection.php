<?php

namespace Brera\Product;

use Brera\SnippetResultList;
use Brera\SnippetRenderer;

class HardcodedProductSnippetRendererCollection extends ProductSnippetRendererCollection
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;
    
    /**
     * @var SnippetRenderer[]
     */
    private $renderers;

    /**
     * @param SnippetRenderer[] $renderers
     * @param SnippetResultList $snippetResultList
     */
    public function __construct(array $renderers, SnippetResultList $snippetResultList)
    {
        $this->renderers = $renderers;
	    $this->snippetResultList = $snippetResultList;
    }

    /**
     * @return SnippetResultList
     */
    protected function getSnippetResultList()
    {
        return $this->snippetResultList;
    }

    /**
     * @return SnippetRenderer[]
     */
    protected function getSnippetRenderers()
    {
        return $this->renderers;
    }
}
