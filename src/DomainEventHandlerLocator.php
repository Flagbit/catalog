<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductImportDomainEvent;

class DomainEventHandlerLocator
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    /**
     * @param IntegrationTestFactory $factory
     */
    public function __construct(IntegrationTestFactory $factory)
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
		    case ProductImportDomainEvent::class :
			    return $this->factory->createProductImportDomainEventHandler($event);
	    }

	    throw new UnableToFindDomainEventHandlerException(
		    sprintf('Unable to find a handler for %s domain event', $eventClass)
	    );
    }
} 
