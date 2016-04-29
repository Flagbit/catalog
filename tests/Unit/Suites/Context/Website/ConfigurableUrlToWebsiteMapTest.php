<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Context\Website\Exception\UnknownWebsiteHostException;

/**
 * @covers \LizardsAndPumpkins\Context\Website\ConfigurableUrlToWebsiteMap
 * @uses   \LizardsAndPumpkins\Context\Website\Website
 */
class ConfigurableUrlToWebsiteMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableUrlToWebsiteMap
     */
    private $websiteMap;

    /**
     * @var string[]
     */
    private $testMap = [
        'example.com' => 'web1',
        '127.0.0.1'   => 'exampleDev',
    ];

    /**
     * @var ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubConfigReader;

    private function assertWebsiteEqual(Website $expected, Website $actual)
    {
        $message = sprintf('Expected website "%s", got "%s"', $expected, $actual);
        $this->assertTrue($actual->isEqual($expected), $message);
    }

    protected function setUp()
    {
        $this->websiteMap = ConfigurableUrlToWebsiteMap::fromArray($this->testMap);
        $this->stubConfigReader = $this->getMock(ConfigReader::class);
    }

    public function testItThrowsAnExceptionIfAHostNameIsNotKnown()
    {
        $this->expectException(UnknownWebsiteHostException::class);
        $this->expectExceptionMessage('No website code found for host "www.example.com"');
        $this->websiteMap->getWebsiteCodeByUrl('www.example.com');
    }

    public function testItReturnsTheCodeIfSet()
    {
        $websiteOne = Website::fromString($this->testMap['example.com']);
        $websiteTwo = Website::fromString($this->testMap['127.0.0.1']);
        $this->assertWebsiteEqual($websiteOne, $this->websiteMap->getWebsiteCodeByUrl('example.com'));
        $this->assertWebsiteEqual($websiteTwo, $this->websiteMap->getWebsiteCodeByUrl('127.0.0.1'));
    }

    public function testItReturnsAWebsiteMapInstance()
    {
        $instance = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
        $this->assertInstanceOf(ConfigurableUrlToWebsiteMap::class, $instance);
    }

    public function testItUsesAMapFromTheConfiguration()
    {
        $map = 'example.com=aaa|127.0.0.1=bbb';
        $this->stubConfigReader->method('get')->with(ConfigurableUrlToWebsiteMap::CONFIG_KEY)->willReturn($map);

        $websiteMap = ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);

        $this->assertWebsiteEqual(Website::fromString('aaa'), $websiteMap->getWebsiteCodeByUrl('example.com'));
        $this->assertWebsiteEqual(Website::fromString('bbb'), $websiteMap->getWebsiteCodeByUrl('127.0.0.1'));
    }

    public function testItThrowsAnExceptionIfAMapValueNotMatchesTheExpectedFormat()
    {
        $this->expectException(InvalidWebsiteMapConfigRecordException::class);
        $this->expectExceptionMessage('Unable to parse the website to code mapping record "test="');
        $map = 'test=';
        $this->stubConfigReader->method('get')->willReturn($map);

        ConfigurableUrlToWebsiteMap::fromConfig($this->stubConfigReader);
    }
}
