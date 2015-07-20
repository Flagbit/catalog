<?php

namespace Brera;

use Brera\Content\ContentBlockWasUpdatedDomainEvent;
use Brera\Content\ContentBlockWasUpdatedDomainEventHandler;
use Brera\Image\ImageImportDomainEvent;
use Brera\Image\ImageImportDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityUpdatedDomainEventHandler;

interface DomainEventFactory
{
    /**
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event);

    /**
     * @param CatalogImportDomainEvent $event
     * @return CatalogImportDomainEventHandler
     */
    public function createCatalogImportDomainEventHandler(CatalogImportDomainEvent $event);

    /**
     * @param RootTemplateChangedDomainEvent $event
     * @return RootTemplateChangedDomainEventHandler
     */
    public function createRootTemplateChangedDomainEventHandler(RootTemplateChangedDomainEvent $event);

    /**
     * @param ImageImportDomainEvent $event
     * @return ImageImportDomainEventHandler
     */
    public function createImageImportDomainEventHandler(ImageImportDomainEvent $event);

    /**
     * @param ProductListingWasUpdatedDomainEvent $event
     * @return ProductListingWasUpdatedDomainEventHandler
     */
    public function createProductListingWasUpdatedDomainEventHandler(ProductListingWasUpdatedDomainEvent $event);

    /**
     * @param ProductStockQuantityUpdatedDomainEvent $event
     * @return ProductStockQuantityUpdatedDomainEventHandler
     */
    public function createProductStockQuantityUpdatedDomainEventHandler(ProductStockQuantityUpdatedDomainEvent $event);

    /**
     * @param ContentBlockWasUpdatedDomainEvent $event
     * @return ContentBlockWasUpdatedDomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(ContentBlockWasUpdatedDomainEvent $event);
}
