<?php

namespace Brera\Api;

use Brera\Environment\Environment;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class ApiRouter implements HttpRouter
{
    const API_URL_PREFIX = 'api';

    /**
     * @var ApiRequestHandlerChain
     */
    private $requestHandlerChain;

    public function __construct(ApiRequestHandlerChain $requestHandlerChain)
    {
        $this->requestHandlerChain = $requestHandlerChain;
    }

    /**
     * @param HttpRequest $request
     * @param Environment $environment
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Environment $environment)
    {
        $urlPath = trim($request->getUrl()->getPath(), DIRECTORY_SEPARATOR);

        $urlToken = explode(DIRECTORY_SEPARATOR, $urlPath);

        if ($this::API_URL_PREFIX !== array_shift($urlToken)) {
            return null;
        }

        $requestHandlerCode = array_shift($urlToken);
        $apiRequestHandler = $this->requestHandlerChain->getApiRequestHandler($requestHandlerCode);

        return $apiRequestHandler;
    }
}
