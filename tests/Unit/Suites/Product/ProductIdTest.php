<?php

namespace Brera\Product;

use Brera\Product\Exception\InvalidProductIdException;

/**
 * @covers \Brera\Product\ProductId
 */
class ProductIdTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateProductIdFromNonString()
    {
        $this->setExpectedException(InvalidProductIdException::class);
        ProductId::fromString(1);
    }

    public function testProductIdCanBeCreatedFromString()
    {
        $productId = ProductId::fromString('foo');
        $this->assertInstanceOf(ProductId::class, $productId);
    }

    public function testProductIdCanBeConvertedToString()
    {
        $productIdString = 'foo';
        $productId = ProductId::fromString($productIdString);

        $this->assertSame($productIdString, (string) $productId);
    }
}
