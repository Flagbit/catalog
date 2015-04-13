<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\DataVersion;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextBuilder
 */
class SearchDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDocumentFieldsCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testContext;

    /**
     * @var string
     */
    private $content = 'foo';

    /**
     * @var SearchDocument
     */
    private $searchDocument;

    protected function setUp()
    {
        $this->stubDocumentFieldsCollection = $this->getMockBuilder(SearchDocumentFieldCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testContext = new VersionedContext(DataVersion::fromVersionString('123'));

        $this->searchDocument = new SearchDocument(
            $this->stubDocumentFieldsCollection,
            $this->testContext,
            $this->content
        );
    }

    /**
     * @test
     */
    public function itShouldCreateSearchDocument()
    {
        $this->assertSame($this->stubDocumentFieldsCollection, $this->searchDocument->getFieldsCollection());
        $this->assertSame($this->testContext, $this->searchDocument->getContext());
        $this->assertSame($this->content, $this->searchDocument->getContent());
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfInputArrayIsEmpty()
    {
        $this->assertFalse($this->searchDocument->hasFieldMatchingOneOf([]));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfNoMatchingFieldIsPresent()
    {
        $this->assertFalse($this->searchDocument->hasFieldMatchingOneOf(['field-name' => 'field-value']));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfAMatchingFieldIsPresent()
    {
        $this->stubDocumentFieldsCollection->expects($this->once())->method('contains')
            ->with($this->isInstanceOf(SearchDocumentField::class))
            ->willReturn(true);
        $this->assertTrue($this->searchDocument->hasFieldMatchingOneOf(['field-name' => 'field-value']));
    }
}
