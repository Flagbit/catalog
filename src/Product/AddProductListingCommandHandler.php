<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

class AddProductListingCommandHandler implements CommandHandler
{
    /**
     * @var AddProductListingCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(AddProductListingCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $productListingMetaInfo = $this->command->getProductListingMetaInfo();
        $urlKey = $productListingMetaInfo->getUrlKey();

        $this->domainEventQueue->add(new ProductListingWasAddedDomainEvent($urlKey, $productListingMetaInfo));
    }
}
