<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\WebsiteContextDecorator;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\ProductId;

abstract class AbstractSearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context
     */
    private $testContext;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @param string[] $fields
     * @param ProductId $productId
     * @return SearchDocument
     */
    private function createSearchDocument(array $fields, ProductId $productId)
    {
        return $this->createSearchDocumentWithContext($fields, $productId, $this->testContext);
    }

    /**
     * @param string[] $fields
     * @param ProductId $productId
     * @param Context $context
     * @return SearchDocument
     */
    private function createSearchDocumentWithContext(array $fields, ProductId $productId, Context $context)
    {
        return new SearchDocument(SearchDocumentFieldCollection::fromArray($fields), $context, $productId);
    }

    /**
     * @param SearchDocumentCollection $collection
     * @param ProductId $productId
     * @return bool
     */
    private function assertCollectionContainsDocumentForProductId(
        SearchDocumentCollection $collection,
        ProductId $productId
    ) {
        foreach ($collection->getDocuments() as $document) {
            if ($document->getProductId() == $productId) {
                $this->assertTrue(true);
                return;
            }
        }
        $this->fail(sprintf('Failed asserting collection contains document for product ID: %s', $productId));
    }

    /**
     * @param SearchDocumentCollection $collection
     * @param ProductId $productId
     * @return bool
     */
    private function assertCollectionDoesNotContainDocumentForProductId(
        SearchDocumentCollection $collection,
        ProductId $productId
    ) {
        foreach ($collection->getDocuments() as $document) {
            if ($document->getProductId() == $productId) {
                $this->fail(
                    sprintf('Failed asserting collection does not contain document for product ID: %s', $productId)
                );
            }
        }
        $this->assertTrue(true);
    }

    /**
     * @param string[] $contextDataSet
     * @return Context
     */
    private function createContextFromDataParts(array $contextDataSet)
    {
        $dataVersion = DataVersion::fromVersionString('-1');
        $contextBuilder = new ContextBuilder($dataVersion);

        return $contextBuilder->createContextsFromDataSets([$contextDataSet])[0];
    }

    /**
     * @param SearchDocument ...$searchDocuments
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollection(SearchDocument ...$searchDocuments)
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getIterator')->willReturn(new \ArrayIterator($searchDocuments));
        $stubSearchDocumentCollection->method('getDocuments')->willReturn($searchDocuments);

        return $stubSearchDocumentCollection;
    }

    protected function setUp()
    {
        $this->searchEngine = $this->createSearchEngineInstance();
        $this->testContext = $this->createContextFromDataParts([WebsiteContextDecorator::CODE => 'ru']);
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testEmptyCollectionIsReturnedIfQueryStringIsNotFoundInIndex()
    {
        $searchDocumentFields = ['foo' => 'bar'];
        $productId = ProductId::fromString(uniqid());
        $searchDocument = $this->createSearchDocument($searchDocumentFields, $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocument);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->query('baz', $this->testContext);

        $this->assertCount(0, $result);
    }

    public function testSearchDocumentsAreAddedToAndRetrievedFromSearchEngine()
    {
        $keyword = 'bar';
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => $keyword], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->query($keyword, $this->testContext);

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testOnlyEntriesContainingRequestedStringAreReturned()
    {
        $keyword = 'bar';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->query($keyword, $this->testContext);

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productBId);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned()
    {
        $keyword = 'bar';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());
        $documentAContext = $this->createContextFromDataParts(['website' => 'value-1']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value-2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value-2']);

        $searchDocumentA = $this->createSearchDocumentWithContext(['foo' => $keyword], $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext(['foo' => $keyword], $productBId, $documentBContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->query($keyword, $queryContext);

        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testPartialContextsAreMatched()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());
        $documentAContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $queryContext = $this->createContextFromDataParts(['locale' => 'value2']);

        $searchDocumentA = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productBId, $documentBContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->query('bar', $queryContext);

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testContextPartsThatAreNotInSearchDocumentContextAreIgnored()
    {
        $productId = ProductId::fromString(uniqid());
        $documentContext = $this->createContextFromDataParts(['locale' => 'value2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);

        $searchDocument = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productId, $documentContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocument);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->query('bar', $queryContext);

        $this->assertCollectionContainsDocumentForProductId($result, $productId);
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => 'barbarism'], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'cabaret'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->query('bar', $this->testContext);

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testEmptyCollectionIsReturnedIfNoSearchDocumentsMatchesGivenCriteria()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);
        $stubCriteria->method('matches')->willReturn(false);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($stubCriteria, $this->testContext);

        $this->assertCount(0, $result);
    }

    public function testCollectionContainsOnlySearchDocumentsMatchingGivenCriteria()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => 'bar'], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $matchingSearchDocumentField = SearchDocumentField::fromKeyAndValues('foo', ['bar']);

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);
        $stubCriteria->method('matches')->willReturnCallback(
            function (SearchDocument $searchDocument) use ($matchingSearchDocumentField) {
                return in_array($matchingSearchDocumentField, $searchDocument->getFieldsCollection()->getFields());
            }
        );

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($stubCriteria, $this->testContext);

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productBId);
    }

    public function testIfMultipleMatchingDocumentsHasSameProductIdOnlyOneInstanceIsReturned()
    {
        $productId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => 'bar'], $productId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);
        $stubCriteria->method('matches')->willReturn(true);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($stubCriteria, $this->testContext);

        $this->assertCollectionContainsDocumentForProductId($result, $productId);
    }

    /**
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance();

    public function testItClearsTheStorage()
    {
        $searchDocumentFieldName = 'foo';
        $searchDocumentFieldValue = 'bar';
        $productId = ProductId::fromString('id');

        $searchDocument = $this->createSearchDocument(
            [$searchDocumentFieldName => $searchDocumentFieldValue],
            $productId
        );

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocument);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $this->searchEngine->clear();
        $this->assertEmpty($this->searchEngine->query($searchDocumentFieldValue, $this->testContext));
    }
}
