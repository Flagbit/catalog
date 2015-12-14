<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\Exception\MalformedSearchCriteriaMetaException;

class ProductListingCriteriaSnippetContent implements PageMetaInfoSnippetContent
{
    const KEY_CRITERIA = 'product_selection_criteria';

    /**
     * @var SearchCriteria
     */
    private $selectionCriteria;

    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var string[]
     */
    private $pageSnippetCodes;

    /**
     * @param SearchCriteria $productSelectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     */
    private function __construct(SearchCriteria $productSelectionCriteria, $rootSnippetCode, array $pageSnippetCodes)
    {
        $this->selectionCriteria = $productSelectionCriteria;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
    }

    /**
     * @param SearchCriteria $selectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductListingCriteriaSnippetContent
     */
    public static function create(SearchCriteria $selectionCriteria, $rootSnippetCode, array $pageSnippetCodes)
    {
        self::validateRootSnippetCode($rootSnippetCode);
        if (!in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }
        return new self($selectionCriteria, $rootSnippetCode, $pageSnippetCodes);
    }

    /**
     * @param string $json
     * @return ProductListingCriteriaSnippetContent
     */
    public static function fromJson($json)
    {
        $pageInfo = self::decodeJson($json);
        self::validateRequiredKeysArePresent($pageInfo);

        self::validateProductListingSearchCriteria($pageInfo[self::KEY_CRITERIA]);
        $searchCriteria = self::createSearchCriteriaFromMetaInfo($pageInfo[self::KEY_CRITERIA]);

        return static::create(
            $searchCriteria,
            $pageInfo[self::KEY_ROOT_SNIPPET_CODE],
            $pageInfo[self::KEY_PAGE_SNIPPET_CODES]
        );
    }

    /**
     * @param mixed[] $pageInfo
     */
    protected static function validateRequiredKeysArePresent(array $pageInfo)
    {
        foreach ([self::KEY_CRITERIA, self::KEY_ROOT_SNIPPET_CODE, self::KEY_PAGE_SNIPPET_CODES] as $key) {
            if (!array_key_exists($key, $pageInfo)) {
                throw new \RuntimeException(sprintf('Missing key in input JSON: "%s"', $key));
            }
        }
    }

    /**
     * @param string $json
     * @return mixed[]
     */
    private static function decodeJson($json)
    {
        $result = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \OutOfBoundsException(sprintf('JSON decode error: %s', json_last_error_msg()));
        }
        return $result;
    }

    /**
     * @return mixed[]
     */
    public function getInfo()
    {
        return [
            self::KEY_CRITERIA => $this->selectionCriteria,
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes
        ];
    }

    /**
     * @param string $rootSnippetCode
     */
    private static function validateRootSnippetCode($rootSnippetCode)
    {
        if (! is_string($rootSnippetCode)) {
            throw new \InvalidArgumentException(sprintf(
                'The page meta info root snippet code has to be a string value, got "%s"',
                gettype($rootSnippetCode)
            ));
        }
    }

    /**
     * @return SearchCriteria
     */
    public function getSelectionCriteria()
    {
        return $this->selectionCriteria;
    }

    /**
     * @return string
     */
    public function getRootSnippetCode()
    {
        return $this->rootSnippetCode;
    }

    /**
     * @return string[]
     */
    public function getPageSnippetCodes()
    {
        return $this->pageSnippetCodes;
    }

    /**
     * @param mixed[] $metaInfo
     * @return SearchCriteria
     */
    private static function createSearchCriteriaFromMetaInfo(array $metaInfo)
    {
        $criterionArray = array_map(function (array $criterionMetaInfo) {
            if (isset($criterionMetaInfo['condition'])) {
                return self::createSearchCriteriaFromMetaInfo($criterionMetaInfo);
            }

            return call_user_func(
                [self::getCriterionClassNameForOperation($criterionMetaInfo['operation']), 'create'],
                $criterionMetaInfo['fieldName'],
                $criterionMetaInfo['fieldValue']
            );
        }, $metaInfo['criteria']);

        return CompositeSearchCriterion::create($metaInfo['condition'], ...$criterionArray);
    }

    /**
     * @param mixed[] $metaInfo
     */
    private static function validateProductListingSearchCriteria(array $metaInfo)
    {
        if (!isset($metaInfo['condition'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criteria condition.');
        }

        if (!isset($metaInfo['criteria'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criteria.');
        }

        array_map(function(array $criteria) {
            if (isset($criteria['condition'])) {
                self::validateProductListingSearchCriteria($criteria);
                return;
            }

            self::validateSearchCriterionMetaInfo($criteria);
        }, $metaInfo['criteria']);
    }

    /**
     * @param mixed[] $criterionArray
     */
    private static function validateSearchCriterionMetaInfo(array $criterionArray)
    {
        if (!isset($criterionArray['fieldName'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criterion field name.');
        }

        if (!isset($criterionArray['fieldValue'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criterion field value.');
        }

        if (!isset($criterionArray['operation'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criterion operation.');
        }

        if (!class_exists(self::getCriterionClassNameForOperation($criterionArray['operation']))) {
            throw new MalformedSearchCriteriaMetaException(
                sprintf('Unknown criterion operation "%s"', $criterionArray['operation'])
            );
        }
    }

    /**
     * @param string $operationName
     * @return string
     */
    private static function getCriterionClassNameForOperation($operationName)
    {
        return SearchCriterion::class . $operationName;
    }
}
