<?php

namespace Brera\Http;

abstract class HttpRequest
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';

    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var HttpHeaders
     */
    private $headers;

    /**
     * @var string[]
     */
    private $lazyLoadedQueryParameters;
    
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
        $headers = HttpHeaders::fromArray(self::getGlobalRequestHeaders());
        $body = HttpRequestBody::fromString($requestBody);

        return self::fromParameters($requestMethod, $url, $headers, $body);
    }

    /**
     * @param string $requestMethod
     * @param HttpUrl $url
     * @param HttpHeaders $headers
     * @param HttpRequestBody $body
     * @return HttpRequest
     * @throws UnsupportedRequestMethodException
     */
    public static function fromParameters($requestMethod, HttpUrl $url, HttpHeaders $headers, HttpRequestBody $body)
    {
        switch (strtoupper($requestMethod)) {
            case self::METHOD_GET:
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
     * @param string $parameterName
     * @return string
     */
    public function getQueryParameter($parameterName)
    {
        if (HttpRequest::METHOD_GET !== $this->getMethod()) {
            return null;
        }

        if (null === $this->lazyLoadedQueryParameters) {
            mb_parse_str($_SERVER['QUERY_STRING'], $this->lazyLoadedQueryParameters);
        }

        if (!isset($this->lazyLoadedQueryParameters[$parameterName])) {
            return null;
        }

        return $this->lazyLoadedQueryParameters[$parameterName];
    }

    /**
     * @return string[]
     */
    private static function getGlobalRequestHeaders()
    {
        return array_reduce(array_keys($_SERVER), function (array $result, $key) {
            return substr($key, 0, 5) !== 'HTTP_' ?
                $result :
                array_merge($result, [strtolower(str_replace('_', '-', substr($key, 5))) => $_SERVER[$key]]);
        }, []);
    }

    /**
     * @return HttpUrl
     */
    public function getUrl()
    {
        return $this->url;
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
}
