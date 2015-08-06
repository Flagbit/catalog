#!/usr/bin/env php
<?php

namespace Brera;

require __DIR__ . '/../vendor/autoload.php';

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());

$eventConsumer = $factory->createDomainEventConsumer();
$eventConsumer->process();
