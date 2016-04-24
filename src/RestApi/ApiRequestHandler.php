<?php

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\DefaultHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;

abstract class ApiRequestHandler implements HttpRequestHandler
{
    /**
     * @param HttpRequest $request
     * @return DefaultHttpResponse
     */
    final public function process(HttpRequest $request)
    {
        try {
            $this->processRequest($request);
            $response = $this->getResponse($request);
        } catch (\Exception $e) {
            /* TODO: Implement error handling */
            throw $e;
        }

        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
            'Content-Type' => 'application/json',
        ];

        return DefaultHttpResponse::create($response->getBody(), $headers);
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    abstract protected function getResponse(HttpRequest $request);

    protected function processRequest(HttpRequest $request)
    {
        // Intentionally empty hook method
    }
}
