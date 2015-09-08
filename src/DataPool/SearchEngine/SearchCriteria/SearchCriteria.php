<?php

namespace Brera\DataPool\SearchEngine\SearchCriteria;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;

interface SearchCriteria
{
    /**
     * @param SearchDocument $searchDocument
     * @return bool
     */
    public function matches(SearchDocument $searchDocument);
}
