<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\FileSearchEngine
 * @covers \Brera\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract
 */
class FileSearchEngineTest extends AbstractSearchEngineTest
{
    /**
     * @var string
     */
    private $temporaryStorage;

    protected function tearDown()
    {
        $this->removeDirectoryAndItsContent($this->temporaryStorage);
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\SearchEngineNotAvailableException
     */
    public function itShouldThrowAnExceptionIfSearchEngineStorageDirIsNotWritable()
    {
        FileSearchEngine::withPath('non-existing-path');
    }

    /**
     * @test
     */
    public function itShouldNotFailIfNoStorageDirectoryIsSpecified()
    {
        $searchEngine = FileSearchEngine::withDefaultPath();

        $this->assertInstanceOf(SearchEngine::class, $searchEngine);
    }

    /**
     * @return SearchEngine
     */
    protected function createSearchEngineInstance()
    {
        $this->prepareTemporaryStorage();

        return FileSearchEngine::withPath($this->temporaryStorage);
    }

    private function prepareTemporaryStorage()
    {
        $this->temporaryStorage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'brera-search-engine-storage';

        if (file_exists($this->temporaryStorage)) {
            $this->removeDirectoryAndItsContent($this->temporaryStorage);
        }

        mkdir($this->temporaryStorage);
    }

    /**
     * @param string $directoryPath
     * @return void
     */
    private function removeDirectoryAndItsContent($directoryPath)
    {
        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($directoryPath);
    }
}
