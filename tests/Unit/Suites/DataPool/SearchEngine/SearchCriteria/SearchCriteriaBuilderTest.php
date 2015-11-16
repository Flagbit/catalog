<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new SearchCriteriaBuilder;
    }

    public function testSearchCriterionEqualIsReturned()
    {
        $parameterName = 'foo';
        $parameterValue = 'bar';
        $result = $this->builder->fromFieldNameAndValue($parameterName, $parameterValue);

        $expectedCriteriaJson = [
            'fieldName' => $parameterName,
            'fieldValue' => $parameterValue,
            'operation' => 'Equal'
        ];

        $this->assertInstanceOf(SearchCriterionEqual::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
    }

    public function testRangeCriterionIsReturnedIsReturned()
    {
        $parameterName = 'foo';
        $rangeFrom = '0';
        $rangeTo = '1';
        $parameterValue = sprintf('%s%s%s', $rangeFrom, SearchEngine::RANGE_DELIMITER, $rangeTo);
        $result = $this->builder->fromFieldNameAndValue($parameterName, $parameterValue);

        $expectedCriteriaJson = [
            'condition' => CompositeSearchCriterion::AND_CONDITION,
            'criteria'  => [
                SearchCriterionGreaterOrEqualThan::create($parameterName, $rangeFrom),
                SearchCriterionLessOrEqualThan::create($parameterName, $rangeTo),
            ]
        ];

        $this->assertInstanceOf(CompositeSearchCriterion::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
    }

    public function testCompositeCriteriaWithListOfFieldsMatchingSameStringAndOrConditionIsReturned()
    {
        $fields = ['foo', 'bar'];
        $queryString = 'baz';
        $result = $this->builder->createCriteriaForAnyOfGivenFieldsContainsString($fields, $queryString);

        $expectedCriteriaJson = [
            'condition' => CompositeSearchCriterion::OR_CONDITION,
            'criteria'  => array_map(function ($fieldName) use ($queryString) {
                return SearchCriterionLike::create($fieldName, $queryString);
            }, $fields)
        ];

        $this->assertInstanceOf(CompositeSearchCriterion::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
    }
}
