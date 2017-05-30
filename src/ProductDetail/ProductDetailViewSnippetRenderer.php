<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer;

class ProductDetailViewSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';

    /**
     * @var ProductDetailViewBlockRenderer
     */
    private $productDetailViewBlockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $productDetailViewSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $productDetailPageMetaSnippetKeyGenerator;

    public function __construct(
        ProductDetailViewBlockRenderer $blockRenderer,
        SnippetKeyGenerator $productDetailViewSnippetKeyGenerator,
        SnippetKeyGenerator $productDetailPageMetaSnippetKeyGenerator
    ) {
        $this->productDetailViewBlockRenderer = $blockRenderer;
        $this->productDetailViewSnippetKeyGenerator = $productDetailViewSnippetKeyGenerator;
        $this->productDetailPageMetaSnippetKeyGenerator = $productDetailPageMetaSnippetKeyGenerator;
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    public function render($productView): array
    {
        if (! $productView instanceof ProductView) {
            throw new InvalidDataObjectTypeException(
                sprintf('Data object must be ProductView, got %s.', typeof($productView))
            );
        }

        return array_merge(
            [$this->createContentSnippet($productView)],
            $this->createProductDetailPageMetaSnippets($productView)
        );
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    private function createProductDetailPageMetaSnippets(ProductView $productView): array
    {
        $pageMetaData = json_encode($this->getPageMetaSnippetContent($productView));
        return array_map(function ($urlKey) use ($pageMetaData, $productView) {
            $key = $this->createPageMetaSnippetKey($urlKey, $productView);
            return Snippet::create($key, $pageMetaData);
        }, $this->getAllProductUrlKeys($productView));
    }

    private function createContentSnippet(ProductView $productView): Snippet
    {
        $key = $this->productDetailViewSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [Product::ID => $productView->getId()]
        );
        $content = $this->productDetailViewBlockRenderer->render($productView, $productView->getContext());

        return Snippet::create($key, $content);
    }

    /**
     * @param ProductView $productView
     * @return mixed[]
     */
    private function getPageMetaSnippetContent(ProductView $productView): array
    {
        $rootBlockName = $this->productDetailViewBlockRenderer->getRootSnippetCode();
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            (string) $productView->getId(),
            $rootBlockName,
            $this->productDetailViewBlockRenderer->getNestedSnippetCodes(),
            []
        );

        return $pageMetaInfo->getInfo();
    }

    private function createPageMetaSnippetKey(string $urlKey, ProductView $productView): string
    {
        return $this->productDetailPageMetaSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );
    }

    /**
     * @param ProductView $productView
     * @return string[]
     */
    private function getAllProductUrlKeys(ProductView $productView): array
    {
        return array_merge(
            [$productView->getFirstValueOfAttribute(Product::URL_KEY)],
            $productView->getAllValuesOfAttribute(Product::NON_CANONICAL_URL_KEY)
        );
    }
}
