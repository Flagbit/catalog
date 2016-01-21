<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\Translation\Exception\UndefinedTranslatorException;

/**
 * @covers \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 */
class TranslatorRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslatorRegistry
     */
    private $registry;

    /**
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createStubTranslatorFactory()
    {
        $stubTranslatorFactory = $this->getMockBuilder(Callback::class)->setMethods(['__invoke'])->getMock();
        $stubTranslatorFactory->method('__invoke')->willReturnCallback(function () {
            return $this->getMock(Translator::class);
        });

        return $stubTranslatorFactory;
    }

    protected function setUp()
    {
        $this->registry = new TranslatorRegistry();
    }

    public function testExceptionIsThrowIfNoTranslatorFactoryIsDefinedForGivenPage()
    {
        $pageCode = 'foo';
        $locale = 'foo_BAR';

        $this->setExpectedException(UndefinedTranslatorException::class);
        $this->registry->getTranslator($pageCode, $locale);
    }

    public function testTranslatorIsReturnedEvenIfLocaleIsNotAvailable()
    {
        $pageCode = 'foo';
        $locale = 'foo_BAR';

        $this->registry->register($pageCode, $this->createStubTranslatorFactory());
        $this->assertInstanceOf(Translator::class, $this->registry->getTranslator($pageCode, $locale));
    }

    public function testSameInstanceOfTranslatorIsReturnedOnConsecutiveCallsForSameLocale()
    {
        $pageCode = 'foo';
        $locale = 'foo_BAR';

        $this->registry->register($pageCode, $this->createStubTranslatorFactory());

        $instanceA = $this->registry->getTranslator($pageCode, $locale);
        $instanceB = $this->registry->getTranslator($pageCode, $locale);

        $this->assertSame($instanceA, $instanceB);
    }

    public function testDifferentInstancesOfTranslatorAreReturnedForDifferentLocales()
    {
        $pageCode = 'foo';
        $localeA = 'foo_BAR';
        $localeB = 'baz_QUX';

        $this->registry->register($pageCode, $this->createStubTranslatorFactory());

        $instanceA = $this->registry->getTranslator($pageCode, $localeA);
        $instanceB = $this->registry->getTranslator($pageCode, $localeB);

        $this->assertNotSame($instanceA, $instanceB);
    }
}
