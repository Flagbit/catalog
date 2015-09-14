<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Stubs\TestContextDecorator;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\Context\WebsiteContextDecorator
 * @uses   \LizardsAndPumpkins\Context\LocaleContextDecorator
 * @uses   \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\DataVersion
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

    public function testNoExceptionIsThrownForIndividualContextCreationWithCodesWithoutMatchingDecorator()
    {
        $result = $this->builder->createContext(['nonExistingContextPartCode' => 'contextPartValue']);
        $this->assertInstanceOf(Context::class, $result);
    }

    public function testExceptionIsThrownForContextListCreationWithDataSetsContainingCodesWithoutMatchingDecorator()
    {
        $this->setExpectedException(ContextDecoratorNotFoundException::class);
        $this->builder->createContextsFromDataSets([['nonExistingContextPartCode' => 'contextPartValue']]);
    }

    public function testNoExceptionIsThrownForDataSetMissingRegisteredDecoratorParts()
    {
        $this->builder->registerContextDecorator('locale', LocaleContextDecorator::class);
        $contexts = [
            ['stub_valid_test' => 'dummy'],
        ];
        foreach ($this->builder->createContextsFromDataSets($contexts) as $context) {
            $this->assertNotContains('locale', $context->getSupportedCodes());
        }
    }

    public function testExceptionIsThrownForNonContextDecoratorClass()
    {
        $this->setExpectedException(InvalidContextDecoratorClassException::class);
        $this->builder->createContext(['stub_invalid_test' => 'dummy']);
    }

    public function testContextsForGivePartsIsReturned()
    {
        $contexts = [
            ['stub_valid_test' => 'dummy'],
        ];
        $result = $this->builder->createContextsFromDataSets($contexts);
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

    public function testExceptionIsThrownIfNonExistentDecoratorClassIsRegistered()
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
        $result = $this->builder->createContextsFromDataSets($contexts);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
    }

    public function testContextIsCreatedFromARequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $result = $this->builder->createFromRequest($stubRequest);
        $this->assertInstanceOf(Context::class, $result);
    }
    
    public function testContextDecoratorsReceiveRequestAsPartOfSourceData()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->builder->registerContextDecorator('request_test', TestContextDecorator::class);
        
        /** @var TestContextDecorator $context */
        $context = $this->builder->createFromRequest($stubRequest);
        
        $rawSourceData = $context->getRawSourceDataForTest();
        $this->assertArrayHasKey('request', $rawSourceData);
        $this->assertSame($stubRequest, $rawSourceData['request']);
    }

    public function testContextDecoratorOrderIsIndependentOfTheContextSourceArrayOrder()
    {
        $contextSourceA = ['stub_valid_test' => 'dummy', 'website' => 'test'];
        $contextSourceB = ['website' => 'test', 'stub_valid_test' => 'dummy'];
        
        $contextA = $this->builder->createContext($contextSourceA);
        $contextB = $this->builder->createContext($contextSourceB);
        
        $this->assertSame($contextA->getId(), $contextB->getId(), 'Context decorator order depends on input');
    }

    public function testContextDecoratorOrderIsIndependentOfTheContextSourceArrayOrderInForDataSets()
    {
        $contextDataSetA = [['stub_valid_test' => 'dummy', 'website' => 'test']];
        $contextDataSetB = [['website' => 'test', 'stub_valid_test' => 'dummy']];
        
        $setA = $this->builder->createContextsFromDataSets($contextDataSetA);
        $setB = $this->builder->createContextsFromDataSets($contextDataSetB);

        $message = 'Context decorators in context sets are not always built in the same order';
        $this->assertSame($setA[0]->getId(), $setB[0]->getId(), $message);
    }

    public function testContextDecoratorOrderIsIndependentOfDecoratorRegistrationOrder()
    {
        $builderA = $this->builder;
        $builderB = new ContextBuilder(DataVersion::fromVersionString('1'));
        
        $builderA->registerContextDecorator('locale', LocaleContextDecorator::class);
        $builderA->registerContextDecorator('website', WebsiteContextDecorator::class);

        $builderB->registerContextDecorator('website', WebsiteContextDecorator::class);
        $builderB->registerContextDecorator('locale', LocaleContextDecorator::class);

        $contextSource = ['request' => $this->getMock(HttpRequest::class, [], [], '', false)];
        $contextA = $builderA->createContext($contextSource);
        $contextB = $builderB->createContext($contextSource);

        $message = 'Context decorator order depends on registration order';
        $this->assertSame($contextA->getId(), $contextB->getId(), $message);
    }
}
