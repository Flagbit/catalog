<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Locale;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\Locale\Locale
 */
class LocaleTest extends TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateLocaleFromNonString()
    {
        $this->expectException(\TypeError::class);
        $invalidLocaleCode = new \stdClass();
        new Locale($invalidLocaleCode);
    }

    public function testLocaleCanBeConvertedToString()
    {
        $localeCode = 'foo_BAR';
        $locale = new Locale($localeCode);
        $this->assertSame($localeCode, (string) $locale);
    }
}
