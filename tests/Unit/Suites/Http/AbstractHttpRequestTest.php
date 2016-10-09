<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\CookieNotSetException;
use LizardsAndPumpkins\Http\Routing\Exception\UnsupportedRequestMethodException;

abstract class AbstractHttpRequestTest extends \PHPUnit_Framework_TestCase
{
    private $testRequestHost = 'example.com';

    private function setUpGlobalState(bool $isSecure = false)
    {
        $_SERVER['REQUEST_METHOD'] = HttpRequest::METHOD_GET;
        $_SERVER['HTTPS'] = $isSecure;
        $_SERVER['HTTP_HOST'] = $this->testRequestHost;
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['QUERY_STRING'] = '';
    }

    public function testUrlIsReturned()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $result = $httpRequest->getUrl();

        $this->assertSame($stubHttpUrl, $result);
    }

    public function testGettingUrlPathWithoutWebsitePrefixIsDelegatedToHttpUrl()
    {
        $path = 'foo';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('getPathWithoutWebsitePrefix')->willReturn($path);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame($path, $httpRequest->getPathWithoutWebsitePrefix());
    }

    public function testGettingUrlPathWithWebsitePrefixIsDelegatedToHttpUrl()
    {
        $path = 'foo';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('getPathWithWebsitePrefix')->willReturn($path);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame($path, $httpRequest->getPathWithWebsitePrefix());
    }

    public function testUnsupportedRequestMethodExceptionIsThrown()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);

        $this->expectException(UnsupportedRequestMethodException::class);
        $this->expectExceptionMessage('Unsupported request method: "XXX"');

        HttpRequest::fromParameters('XXX', $stubHttpUrl, HttpHeaders::fromArray([]), new HttpRequestBody(''));
    }

    public function testHttpIsRequestReturnedFromGlobalState()
    {
        $this->setUpGlobalState();
        $result = HttpRequest::fromGlobalState();

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }

    public function testHttpRequestIsReturnedFromGlobalStateOfSecureUrl()
    {
        $this->setUpGlobalState(true);
        $result = HttpRequest::fromGlobalState();

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }

    public function testItReturnsARequestHeader()
    {
        $this->setUpGlobalState();
        $result = HttpRequest::fromGlobalState();
        $this->assertSame($this->testRequestHost, $result->getHeader('host'));
    }

    public function testItDefaultsToAnEmptyRequestBody()
    {
        $this->setUpGlobalState();
        $result = HttpRequest::fromGlobalState();
        $this->assertSame('', $result->getRawBody());
    }

    public function testItReturnsAnInjectedRequestBody()
    {
        $testRequestBody = 'the request body';
        $this->setUpGlobalState();
        $result = HttpRequest::fromGlobalState($testRequestBody);
        $this->assertSame($testRequestBody, $result->getRawBody());
    }
    
    public function testNullIsReturnedIfParameterIsAbsentInRequestQuery()
    {
        $result = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->assertNull($result->getQueryParameter('foo'));
    }

    public function testQueryParameterRetrievalIsDelegatedToHttpUrl()
    {
        $queryParameterName = 'foo';
        $queryParameterValue = 'bar';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('getQueryParameter')->with($queryParameterName)->willReturn($queryParameterValue);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->assertEquals($queryParameterValue, $request->getQueryParameter($queryParameterName));
    }

    public function testDelegatesToUrlToCheckIfQueryParametersArePresent()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->expects($this->once())->method('hasQueryParameters')->willReturn(true);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertTrue($request->hasQueryParameters());
    }

    public function testArrayOfCookiesIsReturned()
    {
        $expectedCookies = ['foo' => 'bar', 'baz' => 'qux'];

        $originalState = $_COOKIE;
        $_COOKIE = $expectedCookies;

        $request = HttpRequest::fromGlobalState();
        $result = $request->getCookies();

        $_COOKIE = $originalState;

        $this->assertSame($expectedCookies, $result);
    }

    public function testFalseIsReturnedIfRequestedCookieIsNotSet()
    {
        $request = HttpRequest::fromGlobalState();
        $this->assertFalse($request->hasCookie('foo'));
    }

    public function testTrueIsReturnedIfRequestedCookieIsSet()
    {
        $expectedCookieKey = 'foo';

        $originalState = $_COOKIE;
        $_COOKIE[$expectedCookieKey] = 'whatever';

        $request = HttpRequest::fromGlobalState();
        $result = $request->hasCookie($expectedCookieKey);

        $_COOKIE = $originalState;

        $this->assertTrue($result);
    }

    public function testExceptionIsThrownDuringAttemptToGetValueOfCookieWhichIsNotSet()
    {
        $request = HttpRequest::fromGlobalState();
        $this->expectException(CookieNotSetException::class);
        $request->getCookieValue('foo');
    }

    public function testCookieValueIsReturned()
    {
        $expectedCookieName = 'foo';
        $expectedCookieValue = 'bar';

        $originalState = $_COOKIE;
        $_COOKIE = [$expectedCookieName => $expectedCookieValue];

        $request = HttpRequest::fromGlobalState();
        $result = $request->getCookieValue($expectedCookieName);

        $_COOKIE = $originalState;

        $this->assertSame($expectedCookieValue, $result);
    }

    public function testItDelegatesToTheHttpUrlToRetrieveTheRequestHost()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('getHost')->willReturn('example.com');

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame('example.com', $request->getHost());
    }
}
