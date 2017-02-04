<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField
 */
class FacetFilterRequestRangedFieldTest extends TestCase
{
    /**
     * @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAttributeCode;

    /**
     * @var FacetFilterRange|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFilterRange;

    /**
     * @var FacetFilterRequestRangedField
     */
    private $field;

    protected function setUp()
    {
        $this->stubAttributeCode = $this->createMock(AttributeCode::class);
        $this->stubFacetFilterRange = $this->createMock(FacetFilterRange::class);
        $this->field = new FacetFilterRequestRangedField($this->stubAttributeCode);
    }

    public function testFacetFilterRequestFieldInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FacetFilterRequestField::class, $this->field);
    }

    public function testFieldIsRanged()
    {
        $this->assertTrue($this->field->isRanged());
    }

    public function testAttributeCodeIsReturned()
    {
        $this->assertSame($this->stubAttributeCode, $this->field->getAttributeCode());
    }

    public function testArrayOfFacetFilterRangesIsReturned()
    {
        $this->assertContainsOnly(FacetFilterRange::class, $this->field->getRanges());
    }
}
