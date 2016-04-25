<?php

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\InvalidResponseBodyException;
use LizardsAndPumpkins\Http\ContentDelivery\Exception\InvalidStatusCodeException;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpResponse;

class GenericHttpResponse implements HttpResponse
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var HttpHeaders
     */
    private $headers;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @param string $body
     * @param HttpHeaders $headers
     * @param int $statusCode
     */
    private function __construct($body, HttpHeaders $headers, $statusCode)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $body
     * @param string[] $headers
     * @param int $statusCode
     * @return GenericHttpResponse
     */
    public static function create($body, array $headers, $statusCode)
    {
        self::validateResponseBody($body);
        self::validateStatusCode($statusCode);

        $httpHeaders = HttpHeaders::fromArray($headers);

        return new self($body, $httpHeaders, $statusCode);
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function send()
    {
        http_response_code($this->statusCode);
        $this->sendHeaders();
        echo $this->getBody();
    }

    private function sendHeaders()
    {
        foreach ($this->headers->getAll() as $headerName => $headerValue) {
            header(sprintf('%s: %s', $headerName, $headerValue));
        }
    }

    /**
     * @param string $body
     */
    private static function validateResponseBody($body)
    {
        if (! is_string($body)) {
            throw new InvalidResponseBodyException(sprintf('Response body must be a string, got %s.', gettype($body)));
        }
    }

    /**
     * @param int $statusCode
     */
    private static function validateStatusCode($statusCode)
    {
        if (! is_int($statusCode)) {
            throw new InvalidStatusCodeException(
                sprintf('Response status code must be an integer, got %s.', gettype($statusCode))
            );
        }

        if ($statusCode < 100 || $statusCode > 599) {
            throw new InvalidStatusCodeException(
                sprintf('Response status code must be [100-599], got %s.', $statusCode)
            );
        }
    }
}
