<?php


namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

abstract class IntegrationTestSearchEngineAbstract implements SearchEngine
{
    /**
     * @return SearchDocument[]
     */
    abstract protected function getSearchDocuments();

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    final public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        foreach ($searchDocumentCollection->getDocuments() as $searchDocument) {
            $this->addSearchDocument($searchDocument);
        }
    }

    /**
     * @param string $queryString
     * @param Context $queryContext
     * @return string[]
     */
    final public function query($queryString, Context $queryContext)
    {
        $results = [];

        /** @var SearchDocument $searchDocument */
        foreach ($this->getSearchDocuments() as $searchDocument) {
            if (!$this->hasMatchingContext($queryContext, $searchDocument)) {
                continue;
            }

            $results = $this->findMatchingDocumentFields($queryString, $searchDocument, $results);
        }

        return $results;
    }

    /**
     * @param Context $queryContext
     * @param SearchDocument $searchDocument
     * @return bool
     */
    private function hasMatchingContext(Context $queryContext, SearchDocument $searchDocument)
    {
        $hasAtLeastOneMatchingContextPart = false;
        $documentContext = $searchDocument->getContext();
        foreach ($queryContext->getSupportedCodes() as $code) {
            if ($documentContext->supportsCode($code)) {
                if (!$this->hasMatchingContextValue($queryContext, $documentContext, $code)) {
                    return false;
                }
                $hasAtLeastOneMatchingContextPart = true;
            }
        }
        return $hasAtLeastOneMatchingContextPart;
    }

    /**
     * @param Context $queryContext
     * @param Context $documentContext
     * @param string $code
     * @return bool
     */
    private function hasMatchingContextValue(Context $queryContext, Context $documentContext, $code)
    {
        return $queryContext->getValue($code) === $documentContext->getValue($code);
    }

    /**
     * @param string $queryString
     * @param SearchDocument $searchDocument
     * @param string[] $results
     * @return string[]
     */
    private function findMatchingDocumentFields($queryString, SearchDocument $searchDocument, array $results)
    {
        $searchDocumentFieldsCollection = $searchDocument->getFieldsCollection();
        $content = $searchDocument->getContent();
        foreach ($searchDocumentFieldsCollection->getFields() as $field) {
            if (! in_array($content, $results) && false !== stripos($field->getValue(), $queryString)) {
                $results[] = $searchDocument->getContent();
            }
        }
        return $results;
    }
}