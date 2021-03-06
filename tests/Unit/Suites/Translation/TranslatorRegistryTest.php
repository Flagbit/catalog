<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Translation;

use LizardsAndPumpkins\Translation\Exception\UndefinedTranslatorException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Translation\TranslatorRegistry
 */
class TranslatorRegistryTest extends TestCase
{
    /**
     * @var TranslatorRegistry
     */
    private $registry;

    /**
     * @return callable|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubTranslatorFactory() : callable
    {
        $stubTranslatorFactory = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $stubTranslatorFactory->method('__invoke')->willReturnCallback(function () {
            return $this->createMock(Translator::class);
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

        $this->expectException(UndefinedTranslatorException::class);
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

    public function testDifferentInstancesOfTranslatorAreReturnedForDifferentPageCodes()
    {
        $pageCodeA = 'foo';
        $pageCodeB = 'bar';
        $locale = 'foo_BAR';

        $this->registry->register($pageCodeA, $this->createStubTranslatorFactory());
        $this->registry->register($pageCodeB, $this->createStubTranslatorFactory());

        $instanceA = $this->registry->getTranslator($pageCodeA, $locale);
        $instanceB = $this->registry->getTranslator($pageCodeB, $locale);

        $this->assertNotSame($instanceA, $instanceB);
    }
}
