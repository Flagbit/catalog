<?php

namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\CommandHandlerFactory;
use LizardsAndPumpkins\CommandHandlerLocator;
use LizardsAndPumpkins\CommonFactory;
use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Factory;
use LizardsAndPumpkins\FactoryTrait;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\AddProductListingCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductCommandHandler;

class LoggingCommandHandlerFactory implements CommandHandlerFactory, Factory
{
    use FactoryTrait;

    /**
     * @var CommandHandlerFactory
     */
    private $commandFactoryDelegate;

    public function __construct(CommandHandlerFactory $commandFactoryDelegate)
    {
        $this->commandFactoryDelegate = $commandFactoryDelegate;
    }
    
    /**
     * @return CommandHandlerFactory
     */
    private function getCommandFactoryDelegate()
    {
        return $this->commandFactoryDelegate;
    }
    
    /**
     * @param UpdateContentBlockCommand $command
     * @return UpdateContentBlockCommandHandler
     */
    public function createUpdateContentBlockCommandHandler(UpdateContentBlockCommand $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateContentBlockCommandHandler($command)
        );
    }

    /**
     * @param UpdateProductCommand $command
     * @return UpdateProductCommandHandler
     */
    public function createUpdateProductCommandHandler(UpdateProductCommand $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateProductCommandHandler($command)
        );
    }

    /**
     * @param AddProductListingCommand $command
     * @return AddProductListingCommandHandler
     */
    public function createAddProductListingCommandHandler(AddProductListingCommand $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddProductListingCommandHandler($command)
        );
    }

    /**
     * @param AddImageCommand $command
     * @return AddImageCommandHandler
     */
    public function createAddImageCommandHandler(AddImageCommand $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddImageCommandHandler($command)
        );
    }
}
