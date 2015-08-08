<?php

namespace Brera\Http;

class HttpPutRequest extends HttpRequest
{
    /**
     * @return string
     */
    final public function getMethod()
    {
        return self::METHOD_PUT;
    }
}