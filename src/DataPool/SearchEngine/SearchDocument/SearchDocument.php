<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;

class SearchDocument
{
    /**
     * @var SearchDocumentFieldCollection
     */
    private $fields;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $content;

    /**
     * @param SearchDocumentFieldCollection $fields
     * @param Context $context
     * @param string $content
     */
    public function __construct(SearchDocumentFieldCollection $fields, Context $context, $content)
    {
        $this->fields = $fields;
        $this->context = $context;
        $this->content = (string) $content;
    }

    /**
     * @return SearchDocumentFieldCollection
     */
    public function getFieldsCollection()
    {
        return $this->fields;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param SearchCriteria $criteria
     * @return bool
     */
    public function isMatchingCriteria(SearchCriteria $criteria)
    {
        $isMatching = false;
        $canExitTheLoop = false;
        $criterionArray = $criteria->getCriteria();

        while (false === $canExitTheLoop && list(, $criterion) = each($criterionArray)) {
            $isMatching = $this->hasMatchingField($criterion);
            $canExitTheLoop = (SearchCriteria::OR_CONDITION === $criteria->getCondition() && $isMatching) ||
                              (SearchCriteria::AND_CONDITION === $criteria->getCondition() && !$isMatching);
        }

        return $isMatching;
    }

    /**
     * @param SearchCriterion $criterion
     * @return bool
     */
    private function hasMatchingField(SearchCriterion $criterion)
    {
        $isMatching = false;

        /** @var SearchDocumentField $field */
        $fields = $this->fields->getFields();

        while (false === $isMatching && list(, $field) = each($fields)) {
            if ($field->getKey() !== $criterion->getFieldName()) {
                continue;
            }

            $isMatching = $this->searchDocumentFieldMatchesCriterion($criterion, $field);
        }

        return $isMatching;
    }

    /**
     * @param SearchCriterion $criterion
     * @param SearchDocumentField $field
     * @return bool
     */
    private function searchDocumentFieldMatchesCriterion(SearchCriterion $criterion, SearchDocumentField $field)
    {
        switch ($criterion->getOperation()) {
            case 'eq':
                return $field->getValue() == $criterion->getFieldValue();
            case 'neq':
                return $field->getValue() != $criterion->getFieldValue();
            case 'gt':
                return $field->getValue() > $criterion->getFieldValue();
            case 'gte';
                return $field->getValue() >= $criterion->getFieldValue();
            case 'lt':
                return $field->getValue() < $criterion->getFieldValue();
            case 'lte':
                return $field->getValue() <= $criterion->getFieldValue();
        }

        return false;
    }
}
