<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

/**
 * @covers \Brera\DataPool\SearchEngine\CompositeSearchCriterion
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
 */
class CompositeSearchCriterionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[] $searchDocumentFieldsData
     * @return SearchDocument
     */
    private function createStubSearchDocumentWithGivenFields(array $searchDocumentFieldsData)
    {
        $searchDocumentFieldsArray = [];

        foreach ($searchDocumentFieldsData as $fieldKey => $fieldValue) {
            $stubSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
            $stubSearchDocumentField->method('getKey')->willReturn($fieldKey);
            $stubSearchDocumentField->method('getValue')->willReturn($fieldValue);
            $searchDocumentFieldsArray[] = $stubSearchDocumentField;
        }

        $stubSearchDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $stubSearchDocumentFieldsCollection->method('getFields')->willReturn($searchDocumentFieldsArray);

        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldsCollection);

        return $stubSearchDocument;
    }

    public function testSearchCriteriaInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchCriteria::class, CompositeSearchCriterion::createAnd());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, CompositeSearchCriterion::createAnd());
    }

    public function testCriteriaWithAndConditionIsCreated()
    {
        $criteria = CompositeSearchCriterion::createAnd();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }

    public function testCriteriaWithOrConditionIsCreated()
    {
        $criteria = CompositeSearchCriterion::createOr();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => CompositeSearchCriterion::OR_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }
    
    public function testFalseIsReturnedIfNoneOfSearchDocumentFieldsIsNotMatchingCriteria()
    {
        $criteria = CompositeSearchCriterion::createOr();
        $criteria->addCriteria(SearchCriterion::create('foo', 'bar', '='));

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([]);

        $this->assertFalse($criteria->matches($stubSearchDocument));
    }

    public function testFalseIsReturnedIfSearchDocumentFieldsAreNotMatchingAllCriteriaConditions()
    {
        $criteria = CompositeSearchCriterion::createAnd();
        $criteria->addCriteria(SearchCriterion::create('foo', 'bar', '='));
        $criteria->addCriteria(SearchCriterion::create('baz', 'qux', '='));

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => 'bar']);

        $this->assertFalse($criteria->matches($stubSearchDocument));
    }

    public function testTrueIsReturnedIfAllOfSearchDocumentFieldsAreMatchingCriteria()
    {
        $criteria = CompositeSearchCriterion::createAnd();
        $criteria->addCriteria(SearchCriterion::create('foo', 'bar', '='));
        $criteria->addCriteria(SearchCriterion::create('baz', 'qux', '='));

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertTrue($criteria->matches($stubSearchDocument));
    }

    public function testTrueIsReturnedIfAtLeastOneCriteriaConditionMatchesAnyOfSearchDocumentFields()
    {
        $criteria = CompositeSearchCriterion::createOr();
        $criteria->addCriteria(SearchCriterion::create('foo', 'bar', '='));
        $criteria->addCriteria(SearchCriterion::create('baz', 'qux', '='));

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => 'bar']);

        $this->assertTrue($criteria->matches($stubSearchDocument));
    }
}
