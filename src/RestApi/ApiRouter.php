<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRouter;

class ApiRouter implements HttpRouter
{
    const API_URL_PREFIX = 'api';

    /**
     * @var ApiRequestHandlerLocator
     */
    private $requestHandlerLocator;

    public function __construct(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $this->requestHandlerLocator = $requestHandlerLocator;
    }

    /**
     * @param HttpRequest $request
     * @return ApiRequestHandler|null
     */
    public function route(HttpRequest $request)
    {
        $urlPath = trim($request->getPathWithoutWebsitePrefix(), '/');
        $urlToken = explode('/', $urlPath);

        if (self::API_URL_PREFIX !== array_shift($urlToken)) {
            return null;
        }

        if (! $request->hasHeader('Accept') || ! preg_match(
            '/^application\/vnd\.lizards-and-pumpkins\.\w+\.v(\d+)\+(?:json|xml)$/',
            $request->getHeader('Accept'),
            $matchedVersion
        )) {
            return null;
        }

        $requestHandlerCode = array_shift($urlToken);
        if (!$requestHandlerCode) {
            return null;
        }

        $apiRequestHandler = $this->requestHandlerLocator->getApiRequestHandler(
            strtolower($request->getMethod() . '_' . $requestHandlerCode),
            (int)$matchedVersion[1]
        );

        if ($apiRequestHandler->canProcess($request)) {
            return $apiRequestHandler;
        }

        return null;
    }
}
