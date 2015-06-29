<?php

namespace Brera\Tests\Integration;

use Brera\Http\HttpRequest;
use Brera\Http\HttpUrl;
use Brera\IntegrationTestFactory;
use Brera\PoCWebFront;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    public function testApiJsonResponseIsReturned()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $request = HttpRequest::fromParameters('GET', $httpUrl);

        $website = new PoCWebFront($request);
        $website->registerFactory(new IntegrationTestFactory());
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"dummy response"', $response->getBody());
    }
}
