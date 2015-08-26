<?php

namespace Brera\DataPool\SearchEngine;

class SearchCriteria implements \JsonSerializable
{
    const AND_CONDITION = 'and';
    const OR_CONDITION = 'or';

    /**
     * @var string
     */
    private $condition;

    /**
     * @var SearchCriterion[]
     */
    private $criteria = [];

    /**
     * @param string $condition
     */
    private function __construct($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return SearchCriteria
     */
    public static function createAnd()
    {
        return new self(self::AND_CONDITION);
    }

    /**
     * @return SearchCriteria
     */
    public static function createOr()
    {
        return new self(self::OR_CONDITION);
    }

    public function addCriterion(SearchCriterion $criterion)
    {
        $this->criteria[] = $criterion;
    }

    /**
     * @return SearchCriterion[]
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @return bool
     */
    public function hasAndCondition()
    {
        return self::AND_CONDITION === $this->condition;
    }

    /**
     * @return bool
     */
    public function hasOrCondition()
    {
        return self::OR_CONDITION === $this->condition;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'condition' => $this->condition,
            'criteria'  => $this->criteria
        ];
    }
}
