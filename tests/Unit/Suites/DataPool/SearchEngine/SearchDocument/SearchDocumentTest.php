<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\DataVersion;
use Brera\Product\ProductId;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
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
    private $mockDocumentFieldsCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testContext;

    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var SearchDocument
     */
    private $searchDocument;

    protected function setUp()
    {
        $this->mockDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $this->testContext = new VersionedContext(DataVersion::fromVersionString('123'));
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $this->searchDocument = new SearchDocument(
            $this->mockDocumentFieldsCollection,
            $this->testContext,
            $this->stubProductId
        );
    }

    public function testSearchDocumentIsCreated()
    {
        $this->assertSame($this->mockDocumentFieldsCollection, $this->searchDocument->getFieldsCollection());
        $this->assertSame($this->testContext, $this->searchDocument->getContext());
        $this->assertSame($this->stubProductId, $this->searchDocument->getProductId());
    }

    public function testFalseIsReturnedDuringAttemptToMatchSearchDocumentToEmptyCriteria()
    {
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, []);
        $this->assertFalse($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testFalseIsReturnedIfSearchDocumentDoesNotContainAFieldWithMatchingName()
    {
        $dummyFieldValue = 'field-value';

        $criterion = SearchCriterion::create('field-name', $dummyFieldValue, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField('non-matching-field-name', $dummyFieldValue);
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertFalse($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testFalseIsReturnedIfSearchDocumentDoesNotContainAFieldWithValueEqualsToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $criterion = SearchCriterion::create($dummyFieldName, 'field-value', '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, 'non-matching-field-value');
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertFalse($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testTrueIsReturnedIfSearchDocumentContainsAFieldWithValueEqualsToGivenValue()
    {
        $dummyFieldName = 'field-name';
        $dummyFieldValue = 'field-value';

        $criterion = SearchCriterion::create($dummyFieldName, $dummyFieldValue, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, $dummyFieldValue);
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testTrueIsReturnedIfSearchDocumentContainsAFieldWithValueNotEqualToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $criterion = SearchCriterion::create($dummyFieldName, 'field-value', '!=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, 'non-matching-field-value');
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testTrueIsReturnedIfSearchDocumentContainsAFieldWithValueGreaterThenGivenValue()
    {
        $dummyFieldName = 'field-name';

        $criterion = SearchCriterion::create($dummyFieldName, '1', '>');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '2');
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testTrueIsReturnedIfSearchDocumentContainsAFieldWithValueGreaterOrEqualToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $criterion = SearchCriterion::create($dummyFieldName, '1', '>=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '1');
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testTrueIsReturnedIfSearchDocumentContainsAFieldWithValueLessThenGivenValue()
    {
        $dummyFieldName = 'field-name';

        $criterion = SearchCriterion::create($dummyFieldName, '2', '<');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '1');
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testTrueIsReturnedIfSearchDocumentContainsAFieldWithValueLessOrEqualToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $criterion = SearchCriterion::create($dummyFieldName, '1', '<=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '1');
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    public function testSearchDocumentCanBeComparedToMultiLevelCriteria()
    {
        $criterionA = SearchCriterion::create('a', '1', '=');
        $criterionB = SearchCriterion::create('b', '2', '=');

        $mockNestedCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterionA, $criterionB]);

        $criterionC = SearchCriterion::create('c', '3', '>');

        $mockCriteria = $this->createMockCriteria(SearchCriteria::AND_CONDITION, [$criterionC, $mockNestedCriteria]);

        $mockSearchDocumentFieldA = $this->createMockSearchDocumentField('a', '1');
        $mockSearchDocumentFieldC = $this->createMockSearchDocumentField('c', '4');
        $this->mockDocumentFieldsCollection->method('getFields')->willReturn(
            [$mockSearchDocumentFieldA, $mockSearchDocumentFieldC]
        );

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @param string $condition
     * @param \PHPUnit_Framework_MockObject_MockObject[] $mockCriteriaToReturn
     * @return SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCriteria($condition, array $mockCriteriaToReturn)
    {
        $mockCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $mockCriteria->method('hasAndCondition')->willReturn(SearchCriteria::AND_CONDITION === $condition);
        $mockCriteria->method('hasOrCondition')->willReturn(SearchCriteria::OR_CONDITION === $condition);
        $mockCriteria->method('getCriteria')->willReturn($mockCriteriaToReturn);

        return $mockCriteria;
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchDocumentField
     */
    private function createMockSearchDocumentField($fieldName, $fieldValue)
    {
        $mockSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
        $mockSearchDocumentField->method('getKey')->willReturn($fieldName);
        $mockSearchDocumentField->method('getValue')->willReturn($fieldValue);

        return $mockSearchDocumentField;
    }
}
