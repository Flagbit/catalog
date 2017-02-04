<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\Website\Exception\NoConfiguredBaseUrlException;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class WebsiteBaseUrlBuilderTest extends TestCase
{
    private $testBaseUrl = 'http://example.com/';
    
    /**
     * @var WebsiteBaseUrlBuilder
     */
    private $websiteBaseUrlBuilder;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubConfigReader;

    /**
     * @return ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubConfigReader() : ConfigReader
    {
        $stubConfigReader = $this->createMock(ConfigReader::class);
        $configKey = WebsiteBaseUrlBuilder::CONFIG_PREFIX . 'test_website';

        $stubConfigReader->method('has')->willReturnCallback(function (string $requestedConfigKey) use ($configKey) {
            return $configKey === $requestedConfigKey;
        });

        $stubConfigReader->method('get')->with($configKey)->willReturn($this->testBaseUrl);

        return $stubConfigReader;
    }

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContext() : Context
    {
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('getValue')->with(Website::CONTEXT_CODE)->willReturn('test_website');
        return $stubContext;
    }

    protected function setUp()
    {
        $this->stubConfigReader = $this->createStubConfigReader();
        $this->websiteBaseUrlBuilder = new WebsiteBaseUrlBuilder($this->stubConfigReader);

        $this->stubContext = $this->createStubContext();
    }

    public function testItReturnsABaseUrlInstance()
    {
        $this->assertInstanceOf(BaseUrl::class, $this->websiteBaseUrlBuilder->create($this->stubContext));
    }

    public function testItCreatesTheBaseUrlBasedOnTheValueReturnedByTheConfigReader()
    {
        $this->assertSame($this->testBaseUrl, (string) $this->websiteBaseUrlBuilder->create($this->stubContext));
    }

    public function testItImplementsTheBaseUrlBuilderInterface()
    {
        $this->assertInstanceOf(BaseUrlBuilder::class, $this->websiteBaseUrlBuilder);
    }

    public function testItThrowsAnExceptionIfTheConfigReaderReturnsNoValue()
    {
        $this->expectException(NoConfiguredBaseUrlException::class);
        $this->expectExceptionMessage('No base URL configuration found for the website "test_website"');

        /** @var ConfigReader|\PHPUnit_Framework_MockObject_MockObject $emptyStubConfigReader */
        $emptyStubConfigReader = $this->createMock(ConfigReader::class);
        $emptyStubConfigReader->method('has')->willReturn(false);

        (new WebsiteBaseUrlBuilder($emptyStubConfigReader))->create($this->stubContext);
    }
}
