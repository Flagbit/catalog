<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'brera\\poc\\datapoolreader' => '/KeyValue/DataPoolReader.php',
                'brera\\poc\\datapoolwriter' => '/KeyValue/DataPoolWriter.php',
                'brera\\poc\\defaulthttpresponse' => '/DefaultHttpResponse.php',
                'brera\\poc\\domainevent' => '/DomainEvent.php',
                'brera\\poc\\domaineventconsumer' => '/DomainEventConsumer.php',
                'brera\\poc\\domaineventhandler' => '/DomainEventHandler.php',
                'brera\\poc\\domaineventhandlerfailedmessage' => '/DomainEventHandlerFailedMessage.php',
                'brera\\poc\\domaineventhandlerlocator' => '/DomainEventHandlerLocator.php',
                'brera\\poc\\domaineventqueue' => '/Queue/DomainEventQueue.php',
                'brera\\poc\\factory' => '/Factory.php',
                'brera\\poc\\factorytrait' => '/FactoryTrait.php',
                'brera\\poc\\failedtoreadfromdomaineventqueuemessage' => '/FailedToReadFromDomainEventQueueMessage.php',
                'brera\\poc\\frontendfactory' => '/FrontendFactory.php',
                'brera\\poc\\httpgetrequest' => '/HttpGetRequest.php',
                'brera\\poc\\httppostrequest' => '/HttpPostRequest.php',
                'brera\\poc\\httprequest' => '/HttpRequest.php',
                'brera\\poc\\httprequesthandler' => '/HttpRequestHandler.php',
                'brera\\poc\\httpresponse' => '/HttpResponse.php',
                'brera\\poc\\httprouter' => '/HttpRouter.php',
                'brera\\poc\\httprouterchain' => '/HttpRouterChain.php',
                'brera\\poc\\httpsurl' => '/HttpsUrl.php',
                'brera\\poc\\httpurl' => '/HttpUrl.php',
                'brera\\poc\\inmemorydomaineventqueue' => '/Queue/InMemoryDomainEventQueue.php',
                'brera\\poc\\inmemorykeyvaluestore' => '/KeyValue/InMemoryKeyValueStore.php',
                'brera\\poc\\inmemorylogger' => '/InMemoryLogger.php',
                'brera\\poc\\inmemoryproductrepository' => '/Product/InMemoryProductRepository.php',
                'brera\\poc\\integrationtestfactory' => '/IntegrationTestFactory.php',
                'brera\\poc\\keynotfoundexception' => '/KeyValue/KeyNotFoundException.php',
                'brera\\poc\\keyvaluestore' => '/KeyValue/KeyValueStore.php',
                'brera\\poc\\keyvaluestorekeygenerator' => '/KeyValue/KeyValueStoreKeyGenerator.php',
                'brera\\poc\\logger' => '/Logger.php',
                'brera\\poc\\logmessage' => '/LogMessage.php',
                'brera\\poc\\masterfactory' => '/MasterFactory.php',
                'brera\\poc\\masterfactorytrait' => '/MasterFactoryTrait.php',
                'brera\\poc\\nomasterfactorysetexception' => '/NoMasterFactorySetException.php',
                'brera\\poc\\pocmasterfactory' => '/PoCMasterFactory.php',
                'brera\\poc\\pocproductrenderer' => '/Renderer/PoCProductRenderer.php',
                'brera\\poc\\product' => '/Product/Product.php',
                'brera\\poc\\productcreateddomainevent' => '/ProductCreatedDomainEvent.php',
                'brera\\poc\\productcreateddomaineventhandler' => '/ProductCreatedDomainEventHandler.php',
                'brera\\poc\\productdetailhtmlpage' => '/ProductDetailHtmlPage.php',
                'brera\\poc\\productid' => '/Product/ProductId.php',
                'brera\\poc\\productnotfoundexception' => '/Product/ProductNotFoundException.php',
                'brera\\poc\\productrenderer' => '/Renderer/ProductRenderer.php',
                'brera\\poc\\productrepository' => '/Product/ProductRepository.php',
                'brera\\poc\\productseourlrouter' => '/ProductSeoUrlRouter.php',
                'brera\\poc\\singleinstanceregistry' => '/SingleInstanceRegistry.php',
                'brera\\poc\\singleinstanceregistrytrait' => '/SingleInstanceRegistryTrait.php',
                'brera\\poc\\sku' => '/Product/Sku.php',
                'brera\\poc\\unabletorouterequestexception' => '/UnableToRouteRequestException.php',
                'brera\\poc\\undefinedfactorymethodexception' => '/UndefinedFactoryMethodException.php',
                'brera\\poc\\unknownprotocolexception' => '/UnknownProtocolException.php',
                'brera\\poc\\unsupportedrequestmethodexception' => '/UnsupportedRequestMethodException.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    }
);
// @codeCoverageIgnoreEnd
