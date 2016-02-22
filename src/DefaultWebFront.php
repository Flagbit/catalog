<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRouterChain;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Projection\LoggingCommandHandlerFactory;
use LizardsAndPumpkins\Projection\LoggingDomainEventHandlerFactory;

class DefaultWebFront extends WebFront
{
    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return new SampleMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory)
    {
        $commonFactory = new CommonFactory();
        $implementationFactory = new TwentyOneRunFactory();
        $masterFactory->register($commonFactory);
        $masterFactory->register($implementationFactory);
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());
        $masterFactory->register(new FrontendFactory($this->getRequest()));
        //$this->enableDebugLogging($masterFactory, $commonFactory, $implementationFactory);
    }

    private function enableDebugLogging(
        MasterFactory $masterFactory,
        CommonFactory $commonFactory,
        TwentyOneRunFactory $implementationFactory
    ) {
        $masterFactory->register(new LoggingQueueFactory($implementationFactory));
        $masterFactory->register(new LoggingCommandHandlerFactory($commonFactory));
        $masterFactory->register(new LoggingDomainEventHandlerFactory($commonFactory));
    }

    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createProductSearchResultRouter());
        $router->register($this->getMasterFactory()->createProductSearchAutosuggestionRouter());
        $router->register($this->getMasterFactory()->createProductDetailViewRouter());
        $router->register($this->getMasterFactory()->createProductListingRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
