<?php

namespace Brera;

use Brera\Http\HttpRequest;

require_once '../vendor/autoload.php';

$request = HttpRequest::fromGlobalState(file_get_contents('php://input'));

$website = new SampleWebFront($request);
$website->run();
