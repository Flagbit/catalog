<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\SnippetKeyGeneratorLocator;

class ProductListingRequestHandler implements HttpRequestHandler
{
    /**
     * @var ProductListingMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var string
     */
    private $metaInfoSnippetKey;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @param string $metaInfoSnippetKey
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     */
    public function __construct(
        $metaInfoSnippetKey,
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->metaInfoSnippetKey = $metaInfoSnippetKey;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        $this->loadPageMetaInfoSnippet();
        return (bool)$this->pageMetaInfo;
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     * @throws UnableToHandleRequestException
     */
    public function process(HttpRequest $request)
    {
        if (!$this->canProcess($request)) {
            throw new UnableToHandleRequestException;
        }

        $this->addProductsInListingToPageBuilder();

        return $this->pageBuilder->buildPage(
            $this->pageMetaInfo,
            $this->context,
            ['products_per_page' => $this->getDefaultNumberOrProductsPerPage()]
        );
    }

    private function loadPageMetaInfoSnippet()
    {
        if (is_null($this->pageMetaInfo)) {
            $this->pageMetaInfo = false;
            $json = $this->getPageMetaInfoJsonIfExists();
            if ($json) {
                $this->pageMetaInfo = ProductListingMetaInfoSnippetContent::fromJson($json);
            }
        }
    }

    /**
     * @return string
     */
    private function getPageMetaInfoJsonIfExists()
    {
        try {
            $snippet = $this->dataPoolReader->getSnippet($this->metaInfoSnippetKey);
        } catch (KeyNotFoundException $e) {
            $snippet = '';
        }
        return $snippet;
    }

    private function addProductsInListingToPageBuilder()
    {
        $productIds = $this->getProductListingProductIds();

        if (empty($productIds)) {
            return;
        }

        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysFromProductIds($productIds);
        
        $snippetKeyToContentMap = $this->dataPoolReader->getSnippets($productInListingSnippetKeys);
        $snippetCodeToKeyMap = $this->getProductInListingSnippetCodeToKeyMap($productInListingSnippetKeys);

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @return string[]
     */
    private function getProductListingProductIds()
    {
        $selectionCriteria = $this->pageMetaInfo->getSelectionCriteria();
        $productIds = $this->dataPoolReader->getProductIdsMatchingCriteria($selectionCriteria, $this->context);

        return $productIds;
    }

    /**
     * @param string[] $productIds
     * @return string[]
     */
    private function getProductInListingSnippetKeysFromProductIds(array $productIds)
    {
        $snippetCode = ProductInListingInContextSnippetRenderer::CODE;
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
        return array_map(function ($productId) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, ['product_id' => $productId]);
        }, $productIds);
    }

    /**
     * @param string[] $productInListingSnippetKeys
     * @return string[]
     */
    private function getProductInListingSnippetCodeToKeyMap($productInListingSnippetKeys)
    {
        return array_reduce($productInListingSnippetKeys, function (array $acc, $key) {
            $snippetCode = sprintf('product_%d', count($acc) + 1);
            $acc[$snippetCode] = $key;
            return $acc;
        }, []);
    }

    /**
     * @return string
     */
    private function getDefaultNumberOrProductsPerPage()
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            DefaultNumberOfProductsPerPageSnippetRenderer::CODE
        );
        $snippetKey = $keyGenerator->getKeyForContext($this->context, []);
        $defaultNumberOrProductsPerPage = $this->dataPoolReader->getSnippet($snippetKey);

        return $defaultNumberOrProductsPerPage;
    }
}
