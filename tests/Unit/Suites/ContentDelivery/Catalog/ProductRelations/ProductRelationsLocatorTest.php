<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\Exception\InvalidProductRelationTypeException;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\Exception\UnknownProductRelationTypeException;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationTypeCode
 */
class ProductRelationsLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRelationsLocator
     */
    private $productRelationLocator;

    /**
     * @var ProductRelationTypeCode
     */
    private $testRelationTypeCode;

    /**
     * @var ProductRelations|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductRelationType;

    /**
     * @return ProductRelations|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createTestProductRelation()
    {
        return $this->stubProductRelationType;
    }
    
    protected function setUp()
    {
        $this->testRelationTypeCode = ProductRelationTypeCode::fromString('test');
        $this->productRelationLocator = new ProductRelationsLocator();
        $this->stubProductRelationType = $this->getMock(ProductRelations::class);

        $this->productRelationLocator->register($this->testRelationTypeCode, [$this, 'createTestProductRelation']);
    }
    
    public function testItThrowsAnExceptionIfThereIsNoRelationForTheGivenTypeCode()
    {
        $this->setExpectedException(
            UnknownProductRelationTypeException::class,
            'The product relation "unknown" is unknown'
        );
        $this->productRelationLocator->locate(ProductRelationTypeCode::fromString('unknown'));
    }

    public function testItReturnsARegisteredProductRelation()
    {
        $result = $this->productRelationLocator->locate($this->testRelationTypeCode);
        $this->assertSame($this->stubProductRelationType, $result);
    }

    public function testItThrowsAnExceptionIfTheFactoryMethodReturnTypeIsInvalid()
    {
        $typeCode = ProductRelationTypeCode::fromString('invalid');
        $invalidFactoryMethod = function () {
            return new \stdClass();
        };
        $this->productRelationLocator->register($typeCode, $invalidFactoryMethod);
        
        $this->setExpectedException(
            InvalidProductRelationTypeException::class,
            'Product Relation Type "stdClass" has to implement the ProductRelationType interface'
        );
        $this->productRelationLocator->locate($typeCode);
    }
}
