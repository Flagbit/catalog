<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

class ProductStockQuantityWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductStockQuantityWasUpdatedDomainEvent
     */
    private $event;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ProductStockQuantityProjector
     */
    private $projector;

    public function __construct(
        ProductStockQuantityWasUpdatedDomainEvent $event,
        ContextSource $contextSource,
        ProductStockQuantityProjector $projector
    ) {
        $this->event = $event;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productStockQuantitySource = $this->event->getProductStockQuantitySource();

        $this->projector->project($productStockQuantitySource, $this->contextSource);
    }
}
