<?php


namespace Brera\PoC;


class ProductCreatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductCreatedDomainEvent
     */
    private $event;

    /**
     * @var ProductRepository
     */
    private $repository;

    /**
     * @var PoCProductProjector
     */
    private $projector;

    /**
     * @param ProductCreatedDomainEvent $event
     * @param ProductRepository $repository
     */
    public function __construct(
        ProductCreatedDomainEvent $event,
        ProductRepository $repository,
        PoCProductProjector $projector
    )
    {
        $this->event = $event;
        $this->repository = $repository;
        $this->projector = $projector;
    }

    /**
     * @return null
     */
    public function process()
    {
        $productId = $this->event->getProductId();
        $product = $this->repository->findById($productId);
        $this->projector->project($product);
    }
} 

