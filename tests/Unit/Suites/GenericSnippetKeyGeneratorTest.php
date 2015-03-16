<?php


namespace Brera;

use Brera\Context\Context;

/**
 * @covers \Brera\GenericSnippetKeyGenerator
 */
class GenericSnippetKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testSnippetCode = 'test_snippet_code';
    
    /**
     * @var GenericSnippetKeyGenerator
     */
    private $keyGenerator;

    public function setUp()
    {
        $this->keyGenerator = new GenericSnippetKeyGenerator($this->testSnippetCode, ['dummy-context-part']);
    }

    /**
     * @test
     */
    public function itShouldBeASnippetKeyGenerator()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    /**
     * @test
     * @dataProvider invalidTypeSnippetCodeProvider
     * @expectedException \Brera\InvalidSnippetCodeException
     */
    public function itShouldThrowAnExceptionIfTheSnippetCodeIsNoString($invalidSnippetType)
    {
        new GenericSnippetKeyGenerator($invalidSnippetType, ['dummy-context-part']);
    }

    /**
     * @return array[]
     */
    public function invalidTypeSnippetCodeProvider()
    {
        return [
            [12],
            [[]],
            [1.2],
            [new \stdClass()],
        ];
    }

    /**
     * @test
     */
    public function itShouldReturnAKeyIncludingTheHandle()
    {
        $stubContext = $this->getMock(Context::class);
        $result = $this->keyGenerator->getKeyForContext(123, $stubContext);
        $this->assertContains($this->testSnippetCode, $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheSpecifiedIdentifierInTheReturnedKey()
    {
        $stubContext = $this->getMock(Context::class);
        $result = $this->keyGenerator->getKeyForContext(123, $stubContext);
        $this->assertContains('123', $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheContextIdentifierInTheReturnedKey()
    {
        $testContextId = 'test-context-id';
        $stubContext = $this->getMock(Context::class);
        $stubContext->expects($this->once())
            ->method('getIdForParts')
            ->willReturn($testContextId);
        $result = $this->keyGenerator->getKeyForContext(123, $stubContext);
        $this->assertContains($testContextId, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnTheRequiredContextParts()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }
}
