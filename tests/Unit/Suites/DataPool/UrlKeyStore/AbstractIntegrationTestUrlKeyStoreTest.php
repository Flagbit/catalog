<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionToWriteIsEmptyStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\UrlKeyToWriteIsEmptyStringException;
use LizardsAndPumpkins\Util\Storage\Clearable;

abstract class AbstractIntegrationTestUrlKeyStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyStore|Clearable
     */
    private $urlKeyStore;

    /**
     * @return UrlKeyStore
     */
    abstract protected function createUrlKeyStoreInstance();

    protected function setUp()
    {
        $this->urlKeyStore = $this->createUrlKeyStoreInstance();
    }

    public function testItImplementsUrlKeyStore()
    {
        $this->assertInstanceOf(UrlKeyStore::class, $this->urlKeyStore);
    }

    public function testItImplementsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->urlKeyStore);
    }

    public function testItThrowsAnExceptionIfTheUrlKeyIsEmpty()
    {
        $this->expectException(UrlKeyToWriteIsEmptyStringException::class);
        $this->expectExceptionMessage('Invalid URL key: url key strings have to be one or more characters long');
        $this->urlKeyStore->addUrlKeyForVersion('1.0', '', 'dummy-context-string', 'type-string');
    }

    public function testItThrowsAnExceptionIfADataVersionToWriteIsAnEmptyString()
    {
        $this->expectException(DataVersionToWriteIsEmptyStringException::class);
        $this->expectExceptionMessage('Invalid data version: version strings have to be one or more characters long');
        $this->urlKeyStore->addUrlKeyForVersion('', 'test.html', 'dummy-context-string', 'type-string');
    }

    public function testItThrowsAnExceptionIfADataVersionToGetIsAnEmptyString()
    {
        $this->expectException(DataVersionToWriteIsEmptyStringException::class);
        $this->expectExceptionMessage('Invalid data version: version strings have to be one or more characters long');
        $this->urlKeyStore->getForDataVersion('');
    }

    public function testItReturnsUrlKeysForAGivenVersion()
    {
        $testUrlKey = 'example.html';
        $testVersion = '1.0';
        $testContext = 'dummy-context-string';
        $testUrlKeyType = 'type-string';
        $this->urlKeyStore->addUrlKeyForVersion($testVersion, $testUrlKey, $testContext, $testUrlKeyType);
        $this->assertSame(
            [[$testUrlKey, $testContext, $testUrlKeyType]],
            $this->urlKeyStore->getForDataVersion($testVersion)
        );
    }

    public function testItReturnsAnEmptyArrayForUnknownVersions()
    {
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1.0'));
    }

    public function testItReturnsTheUrlKeysForTheGivenVersion()
    {
        $this->urlKeyStore->addUrlKeyForVersion('1', 'aaa.html', 'dummy-context-string', 'type-string');
        $this->urlKeyStore->addUrlKeyForVersion('2', 'bbb.html', 'dummy-context-string', 'type-string');

        $this->assertSame(
            [['aaa.html', 'dummy-context-string', 'type-string']],
            $this->urlKeyStore->getForDataVersion('1')
        );
        $this->assertSame(
            [['bbb.html', 'dummy-context-string', 'type-string']],
            $this->urlKeyStore->getForDataVersion('2')
        );
    }

    public function testItClearsTheStorage()
    {
        $this->urlKeyStore->addUrlKeyForVersion('1', 'aaa.html', 'dummy-context-string', 'type-string');
        $this->urlKeyStore->addUrlKeyForVersion('1', 'bbb.html', 'dummy-context-string', 'type-string');
        $this->urlKeyStore->clear();
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1'));
    }
}
