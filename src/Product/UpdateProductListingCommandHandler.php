<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

class UpdateProductListingCommandHandler implements CommandHandler
{
    /**
     * @var UpdateProductListingCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(UpdateProductListingCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $productListingMetaInfoSource = $this->command->getProductListingMetaInfoSource();
        $urlKey = $productListingMetaInfoSource->getUrlKey();

        $this->domainEventQueue->add(new ProductListingWasUpdatedDomainEvent($urlKey, $productListingMetaInfoSource));
    }
}
