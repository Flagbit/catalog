<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\InvalidWebsiteCodeException;

class WebsiteToCountryMap21RunTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteToCountryMap21Run
     */
    private $websiteToCountryMap;

    protected function setUp()
    {
        $this->websiteToCountryMap = new WebsiteToCountryMap21Run();
    }

    /**
     * @param mixed $invalidWebsiteCode
     * @param string $expectedType
     * @dataProvider invalidWebsiteCodeProvider
     */
    public function testItThrowsAnExceptionIfTheWebsiteCodeIsNotAString($invalidWebsiteCode, $expectedType)
    {
        $this->setExpectedException(
            InvalidWebsiteCodeException::class,
            'The website code must be a string, got "' . $expectedType . '"'
        );
        $this->websiteToCountryMap->getCountry($invalidWebsiteCode);
    }

    /**
     * @return array[]
     */
    public function invalidWebsiteCodeProvider()
    {
        return [
            [123, 'integer'],
            [[], 'array'],
            [$this, get_class($this)]
        ];
    }

    public function testItThrowsAnExceptionIfTheWebsiteCodeIsEmpty()
    {
        $this->setExpectedException(
            InvalidWebsiteCodeException::class,
            'The website code can not be an empty string'
        );
        $this->websiteToCountryMap->getCountry(' ');
    }

    public function testItReturnsGermanyAsTheDefault()
    {
        $this->assertSame('DE', $this->websiteToCountryMap->getCountry('undefined website'));
    }

    /**
     * @dataProvider websiteToCountryDataProvider
     * @param string $websiteCode
     * @param string $expectedCountry
     */
    public function testItReturnsTheCountryForAGivenWebsiteCode($websiteCode, $expectedCountry)
    {
        $this->assertSame($expectedCountry, $this->websiteToCountryMap->getCountry($websiteCode));
    }

    /**
     * @return array[]
     */
    public function websiteToCountryDataProvider()
    {
        return [
            ['ru', 'DE'],
            ['fr', 'FR'],
            ['cy', 'DE'],
        ];
    }
}
