<?php

$baseDir = __DIR__ . '/..';

require_once $baseDir . '/vendor/autoload.php';

use Brera\CommonFactory;
use Brera\Product\CatalogImportDomainEvent;
use Brera\PoCMasterFactory;
use Brera\SampleFactory;

$factory = new PoCMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());

$xml = file_get_contents($baseDir . '/tests/shared-fixture/product.xml');

$queue = $factory->getEventQueue();
$queue->add(new CatalogImportDomainEvent($xml));

$consumer = $factory->createDomainEventConsumer();
$numberOfMessages = 3;
$consumer->process($numberOfMessages);
