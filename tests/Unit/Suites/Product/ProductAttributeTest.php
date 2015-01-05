<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductAttribute
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \DOMElement
	 */
	private $domElement;

	protected function setUp()
	{
		$document = new \DOMDocument();
		$this->domElement = $document->createElement('foo');
	}

	/**
	 * @test
	 */
	public function itShouldReturnTrueIfAttributeWithGivenCodeExists()
	{
		$this->domElement->setAttribute('code', 'name');
		$attribute = ProductAttribute::fromDomElement($this->domElement);

		$this->assertTrue($attribute->hasCode('name'));
	}

	/**
	 * @test
	 */
	public function itShouldReturnFalseIfAttributeWithGivenCodeDoesNotExist()
	{
		$this->domElement->setAttribute('code', 'name');
		$attribute = ProductAttribute::fromDomElement($this->domElement);

		$this->assertFalse($attribute->hasCode('price'));
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeCode()
	{
		$this->domElement->setAttribute('code', 'name');
		$attribute = ProductAttribute::fromDomElement($this->domElement);

		$this->assertEquals('name', $attribute->getCode());
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeValue()
	{
		$this->domElement->setAttribute('code', 'name');
		$this->domElement->nodeValue = 'bar';
		$attribute = ProductAttribute::fromDomElement($this->domElement);

		$this->assertEquals('bar', $attribute->getValue());
	}

	/**
	 * @test
	 * @expectedException \Brera\FirstCharOfAttributeCodeIsNotAlphabeticException
	 * @dataProvider invalidAttributeCodeProvider
	 * @param $invalidAttributeCode
	 */
	public function itShouldThrowAnExceptionIfAttributeCodeStartWithNonAlphabeticCharacter($invalidAttributeCode)
	{
		if (!is_null($invalidAttributeCode)) {
			$this->domElement->setAttribute('code', $invalidAttributeCode);
		}

		ProductAttribute::fromDomElement($this->domElement);
	}

	public function invalidAttributeCodeProvider()
	{
		return [
			[null],
			[''],
			[' '],
			['1'],
			['-bar'],
			['2foo'],
			["\nbaz"]
		];
	}
}
