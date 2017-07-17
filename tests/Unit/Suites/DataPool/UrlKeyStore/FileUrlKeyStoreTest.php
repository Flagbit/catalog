<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Util\FileSystem\Exception\DirectoryDoesNotExistException;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore
 * @uses   \LizardsAndPumpkins\DataPool\UrlKeyStore\IntegrationTestUrlKeyStoreAbstract
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 */
class FileUrlKeyStoreTest extends AbstractIntegrationTestUrlKeyStoreTest
{
    /**
     * @var bool
     */
    public static $createDirectory = true;

    /**
     * @var string
     */
    private $temporaryStoragePath;

    final protected function createUrlKeyStoreInstance(): FileUrlKeyStore
    {
        $this->temporaryStoragePath = $this->prepareTemporaryStorage();

        return new FileUrlKeyStore($this->temporaryStoragePath);
    }

    private function prepareTemporaryStorage(): string
    {
        $temporaryStoragePath = sys_get_temp_dir() . '/lizards-and-pumpkins-test-url-key-storage';

        if (file_exists($temporaryStoragePath)) {
            (new LocalFilesystem())->removeDirectoryAndItsContent($temporaryStoragePath);
        }

        mkdir($temporaryStoragePath);
        return $temporaryStoragePath;
    }

    protected function setUp()
    {
        self::$createDirectory = true;
        parent::setUp();
    }

    protected function tearDown()
    {
        try {
            (new LocalFilesystem())->removeDirectoryAndItsContent($this->temporaryStoragePath);
        } catch (DirectoryDoesNotExistException $e) {
            if (self::$createDirectory) {
                throw $e;
            }
        }

    }

    public function testAddOnOneInstanceReadFromOther()
    {
        $urlKeyStoreOne = $this->createUrlKeyStoreInstance();
        $urlKeyStoreTwo = $this->createUrlKeyStoreInstance();

        $urlKeyStoreOne->addUrlKeyForVersion('1.0', 'example.html', 'dummy-context-string', 'type-string');
        $this->assertSame(
            [['example.html', 'dummy-context-string', 'type-string']],
            $urlKeyStoreTwo->getForDataVersion('1.0')
        );
    }

    public function testItCanStoreContextDataWithASpace()
    {
        $urlKeyStore = $this->createUrlKeyStoreInstance();
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context data with spaces', 'type-string');
        $this->assertSame(
            [['example.html', 'context data with spaces', 'type-string']],
            $urlKeyStore->getForDataVersion('1.0')
        );
    }

    public function testItCreatesTheStorageDirectoryIfItDoesNotExist()
    {
        $urlKeyStore = $this->createUrlKeyStoreInstance();
        rmdir($this->temporaryStoragePath);
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context-data', 'type-string');
        $this->assertFileExists($this->temporaryStoragePath);
        $this->assertTrue(is_dir($this->temporaryStoragePath));
    }

    public function testItThrowsAnExceptionIfDirectoryIsNotCreated()
    {
        $this->expectException(DirectoryDoesNotExistException::class);
        $this->expectExceptionMessageRegExp('#Directory ".*?" was not found and could not be created.#');

        $urlKeyStore = $this->createUrlKeyStoreInstance();
        rmdir($this->temporaryStoragePath);

        self::$createDirectory = false;
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context-data', 'type-string');
    }
}

function mkdir($pathname, $mode = 0777, $recursive = false, $context = null)
{
    if (! FileUrlKeyStoreTest::$createDirectory) {
        return false;
    }
    if ($context !== null) {
        return \mkdir($pathname, $mode, $recursive, $context);
    }
    return \mkdir($pathname, $mode, $recursive);
}
