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
    public function importProductDomainEventShouldPutProductToKeyValueStoreAndSearchIndex()
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
        
        $logger = $factory->getLogger();
        $this->flushErrorsIfAny($logger);

        $dataPoolReader = $factory->createDataPoolReader();
        
        $keyGenerator = $factory->getSnippetKeyGenerator();

        $contextSource = $factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];
        
        $key = $keyGenerator->getKeyForContext('product_detail_view', $productId, $context);
        $html = $dataPoolReader->getSnippet($key);

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

        $key = $keyGenerator->getKeyForContext('product_in_listing', $productId, $context);
        $html = $dataPoolReader->getSnippet($key);

        $this->assertContains(
            (string) $sku,
            $html,
            sprintf('Product in listing snippet HTML does not contain the expected sku "%s"', $sku)
        );
        $this->assertContains(
            $productName,
            $html,
            sprintf('Product in listing snippet HTML does not contain the expected product name "%s"', $productName)
        );

        $searchResults = $dataPoolReader->getSearchResults('led', $context);

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
    public function rootTemplateChangedDomainEventShouldPutProductListingRootSnippetIntoKeyValueStore()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new RootTemplateChangedDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);

        $logger = $factory->getLogger();
        $this->flushErrorsIfAny($logger);

        $dataPoolReader = $factory->createDataPoolReader();
        $keyGenerator = $factory->createProductListingSnippetKeyGenerator();

        $contextSource = $factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $key = $keyGenerator->getKeyForContext('product_listing', 60, $context);
        $html = $dataPoolReader->getSnippet($key);

        $expectation = file_get_contents(__DIR__ . '/../../../theme/template/list.phtml');

        $this->assertContains($expectation, $html);
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

    /**
     * @param Logger $logger
     */
    private function flushErrorsIfAny(Logger $logger)
    {
        $messages = $logger->getMessages();
        if (! empty($messages)) {
            $messagesString = '';
            foreach ($messages as $message) {
                $messagesString .= $message->getException() . PHP_EOL;
            }

            $this->fail($messagesString);
        }
    }
}
