<?php

namespace Brera;

use Brera\Product\ProductSnippetKeyGenerator;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getEventQueue
 * @method Context\ContextBuilder createContextBuilder
 * @method Context\ContextBuilder createContextBuilderWithVersion
 * @method Context\ContextSource createContextSource
 * @method DomainEventConsumer createDomainEventConsumer
 * @method DomainCommandConsumer createDomainCommandConsumer
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method InMemoryLogger getLogger
 * @method GenericSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method ProductSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
