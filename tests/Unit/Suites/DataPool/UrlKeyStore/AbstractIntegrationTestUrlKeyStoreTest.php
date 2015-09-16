<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Utils\Clearable;

abstract class AbstractIntegrationTestUrlKeyStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyStore
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

    public function testItThrowsAnExceptionIfTheUrkKeyToAddIsNotAString()
    {
        $this->setExpectedException(
            Exception\UrlKeyIsNotAStringException::class,
            'URL keys have to be strings for storage in the UrlKeyStore, got '
        );
        $this->urlKeyStore->addUrlKeyForVersion('1.0', 123, 'dummy-context-string');
    }

    public function testItThrowsAnExceptionIfAVersionToAddIsNotAString()
    {
        $this->setExpectedException(
            Exception\DataVersionIsNotAStringException::class,
            'The data version has to be string for use with the UrlKeyStore, got '
        );
        $this->urlKeyStore->addUrlKeyForVersion(123, 'test.html', 'dummy-context-string');
    }

    public function testItThrowsAnExceptionIfTheUrlKeyIsEmpty()
    {
        $this->setExpectedException(
            Exception\UrlKeyToWriteIsEmptyStringException::class,
            'Invalid URL key: url key strings have to be one or more characters long'
        );
        $this->urlKeyStore->addUrlKeyForVersion('1.0', '', 'dummy-context-string');
    }

    public function testItThrowsAnExceptionIfADataVersionToGetUrlKeysForIsNotAString()
    {
        $this->setExpectedException(
            Exception\DataVersionIsNotAStringException::class,
            'The data version has to be string for use with the UrlKeyStore, got '
        );
        $this->urlKeyStore->getForDataVersion(555);
    }

    public function testItThrowsAnExceptionIfADataVersionToWriteIsAnEmptyString()
    {
        $this->setExpectedException(
            Exception\DataVersionToWriteIsEmptyStringException::class,
            'Invalid data version: version strings have to be one or more characters long'
        );
        $this->urlKeyStore->addUrlKeyForVersion('', 'test.html', 'dummy-context-string');
    }

    public function testItThrowsAnExceptionIfADataVersionToGetIsAnEmptyString()
    {
        $this->setExpectedException(
            Exception\DataVersionToWriteIsEmptyStringException::class,
            'Invalid data version: version strings have to be one or more characters long'
        );
        $this->urlKeyStore->getForDataVersion('');
    }

    public function testItThrowsAnExceptionIfTheContextIsNotAString()
    {
        
        $this->setExpectedException(
            Exception\ContextDataIsNotAStringException::class,
            'The context data has to be string for use with the UrlKeyStore, got '
        );
        $this->urlKeyStore->addUrlKeyForVersion('1.0', 'test.html', []);
        
        
        
    }

    public function testItReturnsUrlKeysForAGivenVersion()
    {
        $testUrlKey = 'example.html';
        $testVersion = '1.0';
        $testContext = 'dummy-context-string';
        $this->urlKeyStore->addUrlKeyForVersion($testVersion, $testUrlKey, $testContext);
        $this->assertSame([[$testUrlKey, $testContext]], $this->urlKeyStore->getForDataVersion($testVersion));
    }

    public function testItReturnsAnEmptyArrayForUnknownVersions()
    {
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1.0'));
    }

    public function testItReturnsTheUrlKeysForTheGivenVersion()
    {
        $this->urlKeyStore->addUrlKeyForVersion('1', 'aaa.html', 'dummy-context-string');
        $this->urlKeyStore->addUrlKeyForVersion('2', 'bbb.html', 'dummy-context-string');

        $this->assertSame([['aaa.html', 'dummy-context-string']], $this->urlKeyStore->getForDataVersion('1'));
        $this->assertSame([['bbb.html', 'dummy-context-string']], $this->urlKeyStore->getForDataVersion('2'));
    }

    public function testItClearsTheStorage()
    {
        $this->urlKeyStore->addUrlKeyForVersion('1', 'aaa.html', 'dummy-context-string');
        $this->urlKeyStore->addUrlKeyForVersion('1', 'bbb.html', 'dummy-context-string');
        $this->urlKeyStore->clear();
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1'));
    }
}
