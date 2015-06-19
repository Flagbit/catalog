<?php

namespace Brera\Context;

use Brera\DataVersion;
use Brera\Http\HttpRequest;

/**
 * @covers \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextDecorator
 * @uses   \Brera\DataVersion
 */
class ContextBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new ContextBuilder(DataVersion::fromVersionString('1'));
    }

    public function testExceptionIsThrownForNonExistingCode()
    {
        $this->setExpectedException(ContextDecoratorNotFoundException::class);
        $this->builder->getContext(['foo' => 'bar']);
    }

    public function testExceptionIsThrownForNonContextDecoratorClass()
    {
        $this->setExpectedException(InvalidContextDecoratorClassException::class);
        $this->builder->getContext(['stub_invalid_test' => 'dummy']);
    }

    public function testContextsForGivePartsIsReturned()
    {
        $contexts = [
            ['stub_valid_test' => 'dummy'],
        ];
        $result = $this->builder->getContexts($contexts);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
    }

    /**
     * @dataProvider underscoreCodeDataProvider
     */
    public function testUnderscoresAreRemovesFromContextKey($testCode, $expected)
    {
        $method = new \ReflectionMethod($this->builder, 'removeUnderscores');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke($this->builder, $testCode));
    }

    /**
     * @return array[]
     */
    public function underscoreCodeDataProvider()
    {
        return [
            'no underscores' => ['none', 'none'],
            'one underscore' => ['customer_group', 'customerGroup'],
            'three underscores' => ['test_three_underscores', 'testThreeUnderscores'],
            'underscores front' => ['_front', 'Front'],
            'underscores end' => ['end_', 'end'],
            'consecutive underscores' => ['consecutive__underscores', 'consecutiveUnderscores'],
            'consecutive underscores front' => ['__consecutive_underscores', 'ConsecutiveUnderscores'],
            'consecutive underscores end' => ['consecutive_underscores__', 'consecutiveUnderscores'],
        ];
    }

    public function testExceptionIsThrownIfNonExistentClassIsAdded()
    {
        $this->setExpectedException(ContextDecoratorNotFoundException::class);
        $this->builder->registerContextDecorator('test', 'Non\\Existent\\DecoratorClass');
    }

    public function testExceptionIsThrownIfInvalidDecoratorClassIsAdded()
    {
        $this->setExpectedException(InvalidContextDecoratorClassException::class);
        $this->builder->registerContextDecorator('test', StubInvalidTestContextDecorator::class);
    }

    public function testContextCodesToClassesAreRegistered()
    {
        $this->builder->registerContextDecorator('test', StubValidTestContextDecorator::class);
        $contexts = [
            ['test' => 'dummy'],
        ];
        $result = $this->builder->getContexts($contexts);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
    }

    public function testContextIsCreatedFromARequest()
    {
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $result = $this->builder->createFromRequest($stubRequest);
        $this->assertInstanceOf(Context::class, $result);
    }
}
