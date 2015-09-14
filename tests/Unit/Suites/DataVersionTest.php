<?php

namespace LizardsAndPumpkins;

/**
 * @covers LizardsAndPumpkins\DataVersion
 */
class DataVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider emptyVersionProvider
     * @param mixed $emptyVersion
     */
    public function testExceptionIsThrownIfVersionIsEmpty($emptyVersion)
    {
        $this->setExpectedException(EmptyVersionException::class);
        DataVersion::fromVersionString($emptyVersion);
    }

    /**
     * @return mixed[]
     */
    public function emptyVersionProvider()
    {
        return [
        [''],
        [0],
        [0.00],
        ];
    }

    /**
     * @dataProvider invalidVersionProvider
     * @param mixed $invalidVersion
     */
    public function testExceptionIsThrownIfVersionIsInvalid($invalidVersion)
    {
        $this->setExpectedException(InvalidVersionException::class);
        DataVersion::fromVersionString($invalidVersion);
    }

    /**
     * @return mixed[]
     */
    public function invalidVersionProvider()
    {
        return [
        [null],
        [[]],
        [new \stdClass()],
        [true],
        [false],
        ];
    }

    public function testVersionIsReturned()
    {
        $version = '1.0';
        $dataVersion = DataVersion::fromVersionString($version);
        $this->assertSame($version, (string)$dataVersion);
    }
}
