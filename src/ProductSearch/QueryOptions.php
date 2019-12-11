<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\ProductSearch\Exception\InvalidNumberOfProductsPerPageException;

class QueryOptions
{
    /**
     * @var array[]
     */
    private $filterSelection;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var FacetFiltersToIncludeInResult
     */
    private $facetFiltersToIncludeInResult;

    /**
     * @var int
     */
    private $rowsPerPage;

    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var SortBy
     */
    private $sortBy;

    /**
     * @var null|SearchCriteria
     */
    private $queryFromString;

    /**
     * @var null|SearchCriteria
     */
    private $criteriaFromString;

    /**
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortBy $sortBy
     * @param $queryFromString
     * @param $criteriaFromString
     */
    private function __construct(
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        int $rowsPerPage,
        int $pageNumber,
        SortBy $sortBy,
        $queryFromString,
        $criteriaFromString
    ) {
        $this->filterSelection = $filterSelection;
        $this->context = $context;
        $this->facetFiltersToIncludeInResult = $facetFiltersToIncludeInResult;
        $this->rowsPerPage = $rowsPerPage;
        $this->pageNumber = $pageNumber;
        $this->sortBy = $sortBy;
        $this->queryFromString = $queryFromString;
        $this->criteriaFromString = $criteriaFromString;
    }

    /**
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortBy $sortBy
     * @param $queryFromString
     * @param $criteriaFromString
     * @return QueryOptions
     */
    public static function create(
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        int $rowsPerPage,
        int $pageNumber,
        SortBy $sortBy,
        $queryFromString,
        $criteriaFromString
    ) {
        self::validateRowsPerPage($rowsPerPage);
        self::validatePageNumber($pageNumber);

        return new self(
            $filterSelection,
            $context,
            $facetFiltersToIncludeInResult,
            $rowsPerPage,
            $pageNumber,
            $sortBy,
            $queryFromString,
            $criteriaFromString
        );
    }

    private static function validateRowsPerPage(int $rowsPerPage)
    {
        if ($rowsPerPage <= 0) {
            throw new InvalidNumberOfProductsPerPageException(
                sprintf('Number of rows per page must be positive, got "%s".', $rowsPerPage)
            );
        }
    }

    private static function validatePageNumber(int $pageNumber)
    {
        if ($pageNumber < 0) {
            throw new InvalidNumberOfProductsPerPageException(
                sprintf('Current page number can not be negative, got "%s".', $pageNumber)
            );
        }
    }

    /**
     * @return SearchCriteria|null
     */
    public function getQueryFromString(): ?SearchCriteria
    {
        return $this->queryFromString;
    }

    /**
     * @param SearchCriteria $queryFromString
     */
    public function setQueryFromString(SearchCriteria $queryFromString)
    {
        $this->queryFromString = $queryFromString;
    }

    /**
     * @return SearchCriteria|null
     */
    public function getCriteriaFromString(): ?SearchCriteria
    {
        return $this->criteriaFromString;
    }

    /**
     * @param SearchCriteria $criteriaFromString
     */
    public function setCriteriaFromString(SearchCriteria $criteriaFromString)
    {
        $this->criteriaFromString = $criteriaFromString;
    }

    /**
     * @return array|\array[]
     */
    public function getFilterSelection()
    {
        return $this->filterSelection;
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    public function getFacetFiltersToIncludeInResult() : FacetFiltersToIncludeInResult
    {
        return $this->facetFiltersToIncludeInResult;
    }

    public function getRowsPerPage() : int
    {
        return $this->rowsPerPage;
    }

    public function getPageNumber() : int
    {
        return $this->pageNumber;
    }

    public function getSortBy() : SortBy
    {
        return $this->sortBy;
    }
}
