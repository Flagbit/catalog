<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;

class FileSearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @param string $storagePath
     */
    private function __construct($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * @param string $storagePath
     * @return FileSearchEngine
     */
    public static function create($storagePath)
    {
        if (!is_writable($storagePath)) {
            throw new SearchEngineNotAvailableException(sprintf(
                'Directory "%s" is not writable by the filesystem search engine.',
                realpath($storagePath)
            ));
        }

        return new self($storagePath);
    }

    public function addSearchDocument(SearchDocument $searchDocument)
    {
        file_put_contents($this->storagePath . '/' . uniqid(), serialize($searchDocument));
    }

    /**
     * @return SearchDocument[]
     */
    protected function getSearchDocuments()
    {
        $searchDocuments = [];

        $directoryIterator = new \DirectoryIterator($this->storagePath);

        foreach ($directoryIterator as $entry) {
            if (!$entry->isFile()) {
                continue;
            }

            $filePath = $this->storagePath . '/' . $entry->getFilename();

            $searchDocuments[] = unserialize(file_get_contents($filePath));
        }

        return $searchDocuments;
    }
}
