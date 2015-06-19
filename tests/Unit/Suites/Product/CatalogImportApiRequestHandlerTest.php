<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\CatalogImportApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 */
class CatalogImportApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testProtectedMethodOfConcreteClassIsCalled()
    {
        $result = (new CatalogImportApiRequestHandler())->process();
        $this->assertEquals('"dummy response"', $result->getBody());
    }
}
