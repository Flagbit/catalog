<?php

namespace Brera;

use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductListingSavedDomainEvent;

class DomainEventHandlerLocator
{
    /**
     * @var DomainEventFactory
     */
    private $factory;

    /**
     * @param DomainEventFactory $factory
     */
    public function __construct(DomainEventFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param DomainEvent $event
     * @return DomainEventHandler
     * @throws UnableToFindDomainEventHandlerException
     */
    public function getHandlerFor(DomainEvent $event)
    {
        $eventClass = get_class($event);

        switch ($eventClass) {
            case ProductImportDomainEvent::class:
                return $this->factory->createProductImportDomainEventHandler($event);

            case CatalogImportDomainEvent::class:
                return $this->factory->createCatalogImportDomainEventHandler($event);

            case RootTemplateChangedDomainEvent::class:
                return $this->factory->createRootTemplateChangedDomainEventHandler($event);

            case ProductListingSavedDomainEvent::class:
                return $this->factory->createProductListingSavedDomainEventHandler($event);
        }

        throw new UnableToFindDomainEventHandlerException(
            sprintf('Unable to find a handler for %s domain event', $eventClass)
        );
    }
}
