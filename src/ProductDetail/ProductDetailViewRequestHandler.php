<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

class ProductDetailViewRequestHandler implements HttpRequestHandler
{
    const CODE = 'product_detail';

    /**
     * @var ProductDetailPageMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var TranslatorRegistry
     */
    private $translatorRegistry;

    public function __construct(
        Context $context,
        PageBuilder $pageBuilder,
        TranslatorRegistry $translatorRegistry,
        string $metaInfoJson
    ) {
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->translatorRegistry = $translatorRegistry;
        $this->pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::fromJson($metaInfoJson);
    }

    public function process(HttpRequest $request) : HttpResponse
    {
        $keyGeneratorParams = [Product::ID => $this->pageMetaInfo->getProductId()];

        $this->addTranslationsToPageBuilder($this->context);

        return $this->pageBuilder->buildPage($this->pageMetaInfo, $this->context, $keyGeneratorParams);
    }

    private function addTranslationsToPageBuilder(Context $context)
    {
        $translator = $this->translatorRegistry->getTranslator(
            ProductDetailTemplateSnippetRenderer::CODE,
            $context->getValue(Locale::CONTEXT_CODE)
        );
        $this->addDynamicSnippetToPageBuilder('translations', json_encode($translator));
    }

    private function addDynamicSnippetToPageBuilder(string $snippetCode, string $snippetContents)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }
}
