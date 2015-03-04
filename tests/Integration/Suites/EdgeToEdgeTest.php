<?php

namespace Brera;

use Brera\Http\HttpResourceNotFoundResponse;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\PoCSku;
use Brera\Product\ProductId;
use Brera\Http\HttpUrl;
use Brera\Http\HttpRequest;

class EdgeToEdgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldPutProductToKeyValueStoreAndSearchIndex()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $sku = PoCSku::fromString('118235-251');
        $productId = ProductId::fromSku($sku);
        $productName = 'LED Arm-Signallampe';

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 3;
        $consumer->process($numberOfMessages);
        
        $log = $factory->getLogger();
        $messages = $log->getMessages();
        if (! empty($messages)) {
            $this->fail(implode(PHP_EOL, $messages));
        }
        
        $dataPoolReader = $factory->createDataPoolReader();
        
        $keyGeneratorLocator = $factory->getSnippetKeyGeneratorLocator();
        $keyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode('product_detail_view');

        $contextSource = $factory->createContextSourceBuilder()->createFromXml($xml);
        $context = $contextSource->extractContextsForParts($keyGenerator->getContextParts())[0];
        
        $key = $keyGenerator->getKeyForContext($productId, $context);
        $html = $dataPoolReader->getSnippet($key);

        $searchResults = $dataPoolReader->getSearchResults('led', $context);

        $this->assertContains(
            (string) $sku,
            $html,
            sprintf('The result page HTML does not contain the expected sku "%s"', $sku)
        );
        $this->assertContains(
            $productName,
            $html,
            sprintf('The result page HTML does not contain the expected product name "%s"', $productName)
        );
        $this->assertContains(
            (string) $productId,
            $searchResults,
            sprintf('The search result does not contain the expected product ID "%s"', $productId),
            false,
            false
        );
    }

    /**
     * @test
     */
    public function itShouldPutProductListingRootSnippetIntoKeyValueStore()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new RootSnippetChangedDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);

        $log = $factory->getLogger();
        $messages = $log->getMessages();
        if (! empty($messages)) {
            $this->fail(implode(PHP_EOL, $messages));
        }

        $dataPoolReader = $factory->createDataPoolReader();
        $keyGenerator = $factory->createGenericSnippetKeyGenerator();

        $contextSource = $factory->createContextSourceBuilder()->createFromXml($xml);
        $context = $contextSource->extractContexts(['version', 'website', 'language'])[0];

        $key = $keyGenerator->getKeyForContext('product_listing', $context);
        $html = $dataPoolReader->getSnippet($key);

        $this->assertContains('LIST TEMPLATE', $html);
    }

    /**
     * @test
     */
    public function itShouldMakeAnImportedProductAccessibleFromTheFrontend()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 3;
        $consumer->process($numberOfMessages);
        
        $urlKey = (new XPathParser($xml))->getXmlNodesArrayByXPath('/*/product/attributes/url_key')[0];
        
        $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKey['value']);
        $request = HttpRequest::fromParameters('GET', $httpUrl);

        $website = new PoCWebFront($request, $factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertContains('<body>', $response->getBody());
    }

    /**
     * @test
     */
    public function itShouldReturnAHttpResourceNotFoundResponse()
    {
        $url = HttpUrl::fromString('http://example.com/non/existent/path');
        $request = HttpRequest::fromParameters('GET', $url);

        $website = new PoCWebFront($request);
        $website->registerFactory(new IntegrationTestFactory());
        $response = $website->runWithoutSendingResponse();
        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $response);
    }

    /**
     * @return PoCMasterFactory
     */
    private function prepareIntegrationTestMasterFactory()
    {
        $factory = new PoCMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        $factory->register(new FrontendFactory());
        return $factory;
    }
}
