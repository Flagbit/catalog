<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class LoggingCommandHandlerFactory implements CommandHandlerFactory, Factory
{
    use FactoryTrait;

    /**
     * @var CommandHandler[]
     */
    private $nonDecoratedCommandHandlerDelegates;

    /**
     * @var MasterFactory
     */
    private $masterFactory;

    public function __construct(MasterFactory $masterFactory)
    {
        /** @var CommandHandlerFactory $masterFactory */
        $this->nonDecoratedCommandHandlerDelegates = [
            'UpdateContentBlockCommandHandler' => $masterFactory->createUpdateContentBlockCommandHandler(),
            'UpdateProductCommandHandler' => $masterFactory->createUpdateProductCommandHandler(),
            'AddProductListingCommandHandler' => $masterFactory->createAddProductListingCommandHandler(),
            'AddImageCommandHandler' => $masterFactory->createAddImageCommandHandler(),
            'ShutdownWorkerCommandHandler' => $masterFactory->createShutdownWorkerCommandHandler(),
            'ImportCatalogCommandHandler' => $masterFactory->createImportCatalogCommandHandler(),
            'SetCurrentDataVersionCommandHandler' => $masterFactory->createSetCurrentDataVersionCommandHandler(),
            'UpdateTemplateCommandHandler' => $masterFactory->createUpdateTemplateCommandHandler(),
        ];
        $this->masterFactory = $masterFactory;
    }
    
    private function getDelegate(string $method): CommandHandler
    {
        $key = $this->getClassToInstantiateFromCreateMethod($method);

        return $this->nonDecoratedCommandHandlerDelegates[$key];
    }

    private function getClassToInstantiateFromCreateMethod(string $method): string
    {
        return substr($method, 6);
    }
    
    public function createUpdateContentBlockCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createUpdateProductCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createAddProductListingCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createAddImageCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createShutdownWorkerCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createImportCatalogCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createSetCurrentDataVersionCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createUpdateTemplateCommandHandler(): CommandHandler
    {
        return $this->masterFactory->createProcessTimeLoggingCommandHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }
}
