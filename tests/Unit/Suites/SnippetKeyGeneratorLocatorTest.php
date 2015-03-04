<?php


namespace Brera;

/**
 * @covers \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\GenericSnippetKeyGenerator
 */
class SnippetKeyGeneratorLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->locator = new SnippetKeyGeneratorLocator();
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetCodeException
     * @expectedExceptionMessage Expected snippet code to be a string
     */
    public function itShouldOnlyTakeStringsAsSnippetCodes()
    {
        $mockSnippetRenderer = $this->getMock(SnippetRenderer::class);
        $this->locator->getKeyGeneratorForSnippetCode($mockSnippetRenderer);
    }

    /**
     * @test
     */
    public function itShouldReturnADefaultKeyGeneratorForAnUnknownCode()
    {
        $result = $this->locator->getKeyGeneratorForSnippetCode('test');
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    /**
     * @test
     */
    public function itShouldBePossibleToRegisterKeyGeneratorForSnippetCodes()
    {
        $testSnippetCode = 'test_snippet_code';
        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->locator->register($testSnippetCode, $stubKeyGenerator);
        $this->assertSame($stubKeyGenerator, $this->locator->getKeyGeneratorForSnippetCode($testSnippetCode));
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetCodeException
     * @expectedExceptionMessage Expected snippet code to be a string
     */
    public function itShouldThrowAnExceptionWhenRegisteringANonStringSnippetCode()
    {
        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->locator->register(123, $stubKeyGenerator);
    }

    /**
     * @test
     */
    public function itShouldAlwaysReturnTheSameInstanceForTheSameSnippetCode()
    {
        $result1 = $this->locator->getKeyGeneratorForSnippetCode('test');
        $result2 = $this->locator->getKeyGeneratorForSnippetCode('test');
        $this->assertSame($result1, $result2);
    }

    /**
     * @test
     */
    public function itShouldReturnDifferentInstancesForDifferentSnippetCodes()
    {
        $result1 = $this->locator->getKeyGeneratorForSnippetCode('test1');
        $result2 = $this->locator->getKeyGeneratorForSnippetCode('test2');
        $this->assertNotSame($result1, $result2);
    }
}