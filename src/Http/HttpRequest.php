<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\CookieNotSetException;
use LizardsAndPumpkins\Http\Routing\Exception\UnsupportedRequestMethodException;

abstract class HttpRequest
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';

    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var HttpHeaders
     */
    private $headers;

    /**
     * @var HttpRequestBody
     */
    private $body;

    final public function __construct(HttpUrl $url, HttpHeaders $headers, HttpRequestBody $body)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @param string $requestBody
     * @return HttpRequest
     */
    public static function fromGlobalState($requestBody = '')
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $protocol = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $protocol = 'https';
        }

        $url = HttpUrl::fromString($protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $headers = HttpHeaders::fromGlobalRequestHeaders();
        $body = HttpRequestBody::fromString($requestBody);

        return self::fromParameters($requestMethod, $url, $headers, $body);
    }

    /**
     * @param string $requestMethod
     * @param HttpUrl $url
     * @param HttpHeaders $headers
     * @param HttpRequestBody $body
     * @return HttpRequest
     */
    public static function fromParameters($requestMethod, HttpUrl $url, HttpHeaders $headers, HttpRequestBody $body)
    {
        switch (strtoupper($requestMethod)) {
            case self::METHOD_GET:
            case self::METHOD_HEAD:
                return new HttpGetRequest($url, $headers, $body);
            case self::METHOD_POST:
                return new HttpPostRequest($url, $headers, $body);
            case self::METHOD_PUT:
                return new HttpPutRequest($url, $headers, $body);
            default:
                throw new UnsupportedRequestMethodException(
                    sprintf('Unsupported request method: "%s"', $requestMethod)
                );
        }
    }

    /**
     * @return HttpUrl
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getPathWithoutWebsitePrefix()
    {
        return $this->getUrl()->getPathWithoutWebsitePrefix();
    }

    /**
     * @return string
     */
    public function getPathWithWebsitePrefix()
    {
       return $this->getUrl()->getPathWithWebsitePrefix();
    }

    /**
     * @param string $headerName
     * @return string
     */
    public function getHeader($headerName)
    {
        return $this->headers->get($headerName);
    }

    /**
     * @return string
     */
    public function getRawBody()
    {
        return $this->body->toString();
    }

    /**
     * @return string
     */
    abstract public function getMethod();

    /**
     * @param string $parameterName
     * @return string
     */
    public function getQueryParameter($parameterName)
    {
        return $this->url->getQueryParameter($parameterName);
    }

    /**
     * @return bool
     */
    public function hasQueryParameters()
    {
        return $this->url->hasQueryParameters();
    }

    /**
     * @return string[]
     */
    public function getCookies()
    {
        return $_COOKIE;
    }

    /**
     * @param string $cookieName
     * @return bool
     */
    public function hasCookie($cookieName)
    {
        return isset($_COOKIE[$cookieName]);
    }

    /**
     * @param string $cookieName
     * @return string
     */
    public function getCookieValue($cookieName)
    {
        if (!$this->hasCookie($cookieName)) {
            throw new CookieNotSetException(sprintf('Cookie with "%s" name is not set.', $cookieName));
        }

        return $_COOKIE[$cookieName];
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->url->getHost();
    }
}
