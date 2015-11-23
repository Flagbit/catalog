<?php

chdir(__DIR__ . '/../..');

// Workaround PHPStorm issue to run tests in isolation mode, because phpunit.xml.dist is located in a non-default
// directory relative to the PHPUnit executable.
if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/../../vendor/autoload.php');
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Util/UnitTestFactory.php';

// Closure autoloader from
// https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md#closure-example

spl_autoload_register(function ($class) {
    $prefix = 'LizardsAndPumpkins\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);

    $file = __DIR__ . '/Suites/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
