<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Logging\LoggingQueueFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory;
use LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchFactory;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class DefaultWebFront extends WebFront
{
    protected function createMasterFactory() : MasterFactory
    {
        return new CatalogMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory)
    {
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new ProductSearchFactory());
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());
        $masterFactory->register(new FrontendFactory($this->getRequest()));
        $masterFactory->register($this->getImplementationSpecificFactory());
        //$this->enableDebugLogging($masterFactory, $commonFactory, $implementationFactory);
    }

    private function enableDebugLogging(MasterFactory $masterFactory, CommonFactory $commonFactory)
    {
        $masterFactory->register(new LoggingQueueFactory($this->getImplementationSpecificFactory()));
        $masterFactory->register(new LoggingCommandHandlerFactory($commonFactory));
        $masterFactory->register(new LoggingDomainEventHandlerFactory($commonFactory));
    }

    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createProductSearchResultRouter());
        $router->register($this->getMasterFactory()->createProductDetailViewRouter());
        $router->register($this->getMasterFactory()->createProductListingRouter());
        $router->register($this->getMasterFactory()->createUnknownHttpRequestMethodRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
