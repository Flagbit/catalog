<?php

namespace Brera\Product;

use Brera\DomDocumentXPathParser;
use Brera\Queue\Queue;
use Brera\DomainEventHandler;

class CatalogImportDomainEventHandler implements DomainEventHandler
{
	/**
	 * @var CatalogImportDomainEvent
	 */
	private $event;

	/**
	 * @var Queue
	 */
	private $eventQueue;

	public function __construct(CatalogImportDomainEvent $event, Queue $eventQueue)
	{
		$this->event = $event;
		$this->eventQueue = $eventQueue;
	}

	public function process()
	{
		$xml = $this->event->getXml();

		$productNodesXml = (new DomDocumentXPathParser($xml))->getXPathNodeXml('product');
		foreach ($productNodesXml as $productXml) {
			$this->eventQueue->add(new ProductImportDomainEvent($productXml));
		}
	}
} 
