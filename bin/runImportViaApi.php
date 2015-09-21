#!/usr/bin/env php
<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpRouterChain;
use LizardsAndPumpkins\Http\HttpUrl;

require_once __DIR__ . '/../vendor/autoload.php';

class ApiApp extends WebFront
{
    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return new SampleMasterFactory();
    }

    protected function registerFactories(MasterFactory $factory)
    {
        $factory->register(new CommonFactory());
        $factory->register(new SampleFactory());
        $factory->register(new LoggingQueueFactory());
        $factory->register(new FrontendFactory($this->getRequest()));
    }

    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
    
    public function processQueues()
    {
        $commandConsumer = $this->getMasterFactory()->createCommandConsumer();
        $commandConsumer->process();
        $domainEventConsumer = $this->getMasterFactory()->createDomainEventConsumer();
        $domainEventConsumer->process();
    }
}


$httpRequestBodyContent = file_get_contents(__DIR__ . '/../tests/shared-fixture/product-listing-root-snippet.json');
$productListingImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/templates/product_listing'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json']),
    HttpRequestBody::fromString($httpRequestBodyContent)
);
$productListingImport = new ApiApp($productListingImportRequest);
$productListingImport->runWithoutSendingResponse();


$productSearchAutosuggestionImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/templates/product_search_autosuggestion'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json']),
    HttpRequestBody::fromString('')
);
$productSearchAutosuggestionImport = new ApiApp($productSearchAutosuggestionImportRequest);
$productSearchAutosuggestionImport->runWithoutSendingResponse();


$catalogImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/catalog_import'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json']),
    HttpRequestBody::fromString(json_encode(['fileName' => 'catalog.xml']))
);

$catalogImport = new ApiApp($catalogImportRequest);
$catalogImport->runWithoutSendingResponse();

$catalogImport->processQueues();
