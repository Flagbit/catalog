<?php

namespace LizardsAndPumpkins\ProductRecommendations\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\ProductRecommendations\Exception\UnableToProcessProductRelationsRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\Product\ProductId;

class ProductRelationsApiV1GetRequestHandler extends ApiRequestHandler
{
    /**
     * @var ProductRelationsService
     */
    private $productRelationsService;

    public function __construct(ProductRelationsService $productRelationsService)
    {
        $this->productRelationsService = $productRelationsService;
    }
    
    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        if ($request->getMethod() !== HttpRequest::METHOD_GET) {
            return false;
        }
        // Matching path example: /api/products/example-sku/relations/upsells
        $parts = $this->getRequestPathParts($request);
        return count($parts) > 4 && 'products' === $parts[1] && 'relations' === $parts[3];
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    final protected function getResponse(HttpRequest $request)
    {
        if (! $this->canProcess($request)) {
            throw $this->getUnableToProcessRequestException($request);
        }
        
        $relatedProductsData = $this->productRelationsService->getRelatedProductData(
            $this->getProductRelationTypeCode($request),
            $this->getProductId($request)
        );

        $headers = [];
        $body = json_encode(['data' => $relatedProductsData]);

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_OK);
    }

    /**
     * @param HttpRequest $request
     * @return string[]
     */
    private function getRequestPathParts(HttpRequest $request)
    {
        return explode('/', trim($request->getUrlPathRelativeToWebFront(), '/'));
    }

    /**
     * @param HttpRequest $request
     * @return ProductId
     */
    private function getProductId(HttpRequest $request)
    {
        return ProductId::fromString($this->getRequestPathParts($request)[2]);
    }

    /**
     * @param HttpRequest $request
     * @return ProductRelationTypeCode
     */
    private function getProductRelationTypeCode(HttpRequest $request)
    {
        return ProductRelationTypeCode::fromString($this->getRequestPathParts($request)[4]);
    }

    /**
     * @param HttpRequest $request
     * @return UnableToProcessProductRelationsRequestException
     */
    private function getUnableToProcessRequestException(HttpRequest $request)
    {
        $requestPath = $request->getUrlPathRelativeToWebFront();
        $message = sprintf('Unable to process a %s request to "%s"', $request->getMethod(), $requestPath);
        return new UnableToProcessProductRelationsRequestException($message);
    }
}
