<?php

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidMessageMetadataException;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 */
class MessageMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionForNonStringKeys()
    {
        $this->expectException(InvalidMessageMetadataException::class);
        $this->expectExceptionMessage('The message metadata may only have string array keys');
        new MessageMetadata([0 => 'foo']);
    }

    public function testThrowsExceptionIfArrayKeyIsEmptyString()
    {
        $this->expectException(InvalidMessageMetadataException::class);
        $this->expectExceptionMessage('The message metadata array keys must not be empty');
        new MessageMetadata(['' => 'foo']);
    }

    /**
     * @param mixed $invalidValue
     * @param string $expectedType
     * @dataProvider invalidMetadataValueTypeProvider
     */
    public function testThrowsExceptionIfValueIsNotStringOrBoolOrIntOrDouble($invalidValue, string $expectedType)
    {
        $this->expectException(InvalidMessageMetadataException::class);
        $message = 'The message metadata values may only me strings, booleans, integers or doubles,' .
            ' got ' . $expectedType;
        $this->expectExceptionMessage($message);
        new MessageMetadata(['bar' => $invalidValue]);
    }

    /**
     * @return array[]
     */
    public function invalidMetadataValueTypeProvider() : array
    {
        return [
            'object' => [$this, get_class($this)],
            'null' => [null, 'NULL'],
            'resource' => [fopen(__FILE__, 'r'), 'resource'],
        ];
    }

    public function testReturnsTheInjectedMetadataArray()
    {
        $metadata = ['foo' => 'bar', 'buz' => 12, 'qux' => true, 'moo' => 47.5];
        $this->assertSame($metadata, (new MessageMetadata($metadata))->getMetadata());
    }
}
