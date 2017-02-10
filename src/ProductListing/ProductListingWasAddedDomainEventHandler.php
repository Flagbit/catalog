<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector;

class ProductListingWasAddedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductListingSnippetProjector
     */
    private $projector;

    public function __construct(ProductListingSnippetProjector $projector)
    {
        $this->projector = $projector;
    }

    public function process(Message $message)
    {
        $domainEvent = ProductListingWasAddedDomainEvent::fromMessage($message);
        $this->projector->project($domainEvent->getListingCriteria());
    }
}
