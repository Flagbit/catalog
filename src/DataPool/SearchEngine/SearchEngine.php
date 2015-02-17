<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Environment\Environment;

interface SearchEngine
{
    /**
     * @param SearchDocument $searchDocument
     * @return void
     */
    public function addSearchDocument(SearchDocument $searchDocument);

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection);

    /**
     * @param string $queryString
     * @param Environment $environment
     * @return string[]
     */
    public function query($queryString, Environment $environment);
}
