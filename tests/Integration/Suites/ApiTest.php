<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class ApiTest extends AbstractIntegrationTest
{
    public function testDomainEventsArePlacedIntoQueue()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        
        $commandQueue = $factory->getCommandMessageQueue();
        $this->assertEquals(0, $commandQueue->count());

        $website = new InjectableDefaultWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();
        
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
        $this->assertGreaterThan(0, $commandQueue->count());
    }
}
