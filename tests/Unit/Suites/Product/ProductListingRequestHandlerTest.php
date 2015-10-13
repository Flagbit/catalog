<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class ProductListingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $testMetaInfoKey = 'stub-meta-info-key';

    /**
     * @var int
     */
    private $testDefaultNumberOfProductsPerPage = 1;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductInListingSnippetKeyGenerator;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchCriteriaBuilder;

    private function prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection()
    {
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();
        $this->prepareMockDataPoolReaderWithStubSearchDocumentCollection($stubSearchDocumentCollection);
    }

    private function prepareMockDataPoolReaderWithStubSearchDocumentCollection(
        \PHPUnit_Framework_MockObject_MockObject $documentCollection
    ) {
        $this->prepareMockDataPoolReader();

        $stubFacetFieldsCollection = $this->getMock(SearchEngineFacetFieldCollection::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getSearchDocuments')->willReturn($documentCollection);
        $stubSearchEngineResponse->method('getFacetFieldCollection')->willReturn($stubFacetFieldsCollection);

        $this->mockDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);
    }

    private function prepareMockDataPoolReader()
    {
        /** @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject $stubSelectionCriteria */
        $stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);

        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingCriteriaSnippetContent::create(
            $stubSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes
        )->getInfo());

        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson]
        ]);

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
    }

    /**
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollection()
    {
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);
        $stubSearchDocumentCollection->method('count')->willReturn(1);

        return $stubSearchDocumentCollection;
    }

    /**
     * @return SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSnippetKeyGeneratorLocator()
    {
        $this->stubProductInListingSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductInListingSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn('stub-product-snippet-key');

        $stubProductListingCriteriaSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
        $stubProductListingCriteriaSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);
        
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturnMap([
            [ProductListingCriteriaSnippetRenderer::CODE, $stubProductListingCriteriaSnippetKeyGenerator],
            [ProductInListingSnippetRenderer::CODE, $this->stubProductInListingSnippetKeyGenerator],
        ]);

        return $stubSnippetKeyGeneratorLocator;
    }

    /**
     * @param ProductId[] $productIds
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollectionContainingDocumentsWithGivenProductIds(array $productIds)
    {
        $stubSearchDocuments = array_map(function (ProductId $productId) {
            $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
            $stubSearchDocument->method('getProductId')->willReturn($productId);
            return $stubSearchDocument;
        }, $productIds);

        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn($stubSearchDocuments);
        $stubSearchDocumentCollection->method('count')->willReturn(count($stubSearchDocuments));

        return $stubSearchDocumentCollection;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy
     * @param ProductId ...$expectedProductIds
     */
    private function assertKeyGeneratorWasOnlyTriggeredWithGivenProductIds(
        \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy,
        ProductId ...$expectedProductIds
    ) {
        $invokedProductIds = array_reduce(
            $spy->getInvocations(),
            function (array $carry, \PHPUnit_Framework_MockObject_Invocation_Object $invocation) {
                if (is_array($invocation->parameters[1]) && isset($invocation->parameters[1][Product::ID])) {
                    $carry[] = $invocation->parameters[1][Product::ID];
                }
                return $carry;
            },
            []
        );

        $this->assertEquals($expectedProductIds, $invokedProductIds);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private function createAddedSnippetsSpy()
    {
        $addSnippetsToPageSpy = $this->any();
        $this->mockPageBuilder->expects($addSnippetsToPageSpy)->method('addSnippetsToPage');
        return $addSnippetsToPageSpy;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy
     * @param string $snippetCode
     */
    private function assertDynamicSnippetWasAddedToPageBuilder(
        \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy,
        $snippetCode
    ) {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(array_map(function ($invocation) use ($snippetCode) {
            return intval([$snippetCode => $snippetCode] === $invocation->parameters[0]);
        }, $spy->getInvocations()));

        $this->assertEquals(
            1,
            $numberOfTimesSnippetWasAddedToPageBuilder,
            sprintf('Failed to assert "%s" snippet was added to page builder.', $snippetCode)
        );
    }

    protected function setUp()
    {
        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();

        $stubFilterNavigationAttributeCodes = ['foo'];

        $this->mockSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class);

        $this->requestHandler = new ProductListingRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $stubFilterNavigationAttributeCodes,
            $this->testDefaultNumberOfProductsPerPage,
            $this->mockSearchCriteriaBuilder
        );

        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testFalseIsReturnedIfThePageMetaInfoContentSnippetCanNotBeLoaded()
    {
        $exception = new KeyNotFoundException();
        $this->mockDataPoolReader->method('getSnippet')->willThrowException($exception);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testTrueIsReturnedIfThePageMetaInfoContentSnippetCanBeLoaded()
    {
        $this->prepareMockDataPoolReader();
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testPageMetaInfoIsOnlyLoadedOnce()
    {
        $this->prepareMockDataPoolReader();

        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);

        $this->requestHandler->canProcess($this->stubRequest);
        $this->requestHandler->canProcess($this->stubRequest);
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testPageMetaInfoSnippetIsCreated()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->requestHandler->process($this->stubRequest);

        $this->assertAttributeInstanceOf(
            ProductListingCriteriaSnippetContent::class,
            'pageMetaInfo',
            $this->requestHandler
        );
    }

    public function testPageIsReturned()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->mockPageBuilder->method('buildPage')->willReturn($this->getMock(HttpResponse::class, [], [], '', false));

        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testNoSnippetsAreAddedToPageBuilderIfListingIsEmpty()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('count')->willReturn(0);
        $this->prepareMockDataPoolReaderWithStubSearchDocumentCollection($stubSearchDocumentCollection);

        $this->mockPageBuilder->expects($this->never())->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testProductsInListingAreAddedToPageBuilder()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();

        $this->mockPageBuilder->expects($this->atLeastOnce())->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testSelectedFiltersAreNotAppliedToEmptyCollection()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('count')->willReturn(0);
        $stubSearchDocumentCollection->expects($this->never())->method('getCollectionFilteredByCriteria');
        $this->prepareMockDataPoolReaderWithStubSearchDocumentCollection($stubSearchDocumentCollection);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testFiltersAreAppliedToSelectionCriteriaIfSelected()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();

        $this->stubRequest->method('getQueryParameter')->willReturnMap([['foo', 'bar']]);

        $stubCriteria = $this->getMock(SearchCriteria::class);
        $this->mockSearchCriteriaBuilder->expects($this->once())->method('fromRequestParameter')->with('foo', 'bar')
            ->willReturn($stubCriteria);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testRangeFiltersAreAppliedToSelectionCriteriaIfSelected()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();

        $attributeCode = 'foo';
        $fromRange = '1';
        $toRange = '2';

        $filterValue = sprintf('%s%s%s', $fromRange, SearchCriteriaBuilder::FILTER_RANGE_DELIMITER, $toRange);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([[$attributeCode, $filterValue]]);

        $stubCriteria = $this->getMock(SearchCriteria::class);
        $this->mockSearchCriteriaBuilder->expects($this->once())->method('fromRequestParameter')
            ->with($attributeCode, $filterValue)->willReturn($stubCriteria);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testFilterNavigationSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'filter_navigation';
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode);
    }

    public function testTotalPagesCountSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'total_pages_count';
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode);
    }

    public function testCollectionSizeSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'collection_size';
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode);
    }
}
