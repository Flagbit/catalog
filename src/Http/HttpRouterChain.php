<?php

namespace LizardsAndPumpkins\Http;

class HttpRouterChain implements HttpRouter
{
    /**
     * @var HttpRouter[]
     */
    private $routers = [];

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request)
    {
        foreach ($this->routers as $router) {
            $handler = $router->route($request);
            if (null !== $handler) {
                return $handler;
            }
        }
        throw new UnableToRouteRequestException(sprintf('Unable to route a request "%s"', $request->getUrl()));
    }

    public function register(HttpRouter $router)
    {
        $this->routers[] = $router;
    }
}
