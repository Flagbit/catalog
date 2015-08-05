<?php

namespace Brera\Http;

class ResourceNotFoundRouter implements HttpRouter
{
    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request)
    {
        return new ResourceNotFoundRequestHandler();
    }
}
