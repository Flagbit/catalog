<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\NoSelectedSortOrderException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Product\AttributeCode;

class ProductListingPageRequest
{
    const PRODUCTS_PER_PAGE_COOKIE_NAME = 'products_per_page';
    const PRODUCTS_PER_PAGE_COOKIE_TTL = 3600 * 24 * 30;
    const PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME = 'limit';
    const SORT_ORDER_COOKIE_NAME = 'sort_order';
    const SORT_DIRECTION_COOKIE_NAME = 'sort_direction';
    const SORT_ORDER_COOKIE_TTL = 3600 * 24 * 30;
    const SORT_DIRECTION_COOKIE_TTL = 3600 * 24 * 30;
    const SORT_ORDER_QUERY_PARAMETER_NAME = 'order';
    const SORT_DIRECTION_QUERY_PARAMETER_NAME = 'dir';
    const PAGINATION_QUERY_PARAMETER_NAME = 'p';

    /**
     * @var SortOrderConfig[]
     */
    private $sortOrderConfigs;

    /**
     * @var ProductsPerPage
     */
    private $productsPerPage;

    public function __construct(ProductsPerPage $productsPerPage, SortOrderConfig ...$sortOrderConfigs)
    {
        $this->productsPerPage = $productsPerPage;
        $this->sortOrderConfigs = $sortOrderConfigs;
    }

    /**
     * @param HttpRequest $request
     * @return int
     */
    public function getCurrentPageNumber(HttpRequest $request)
    {
        return max(0, $request->getQueryParameter(self::PAGINATION_QUERY_PARAMETER_NAME) - 1);
    }

    /**
     * @param HttpRequest $request
     * @param string[] $filterNavigationConfig
     * @return array[]
     */
    public function getSelectedFilterValues(HttpRequest $request, array $filterNavigationConfig)
    {
        return array_reduce(array_keys($filterNavigationConfig), function (array $carry, $filterName) use ($request) {
            $carry[$filterName] = array_filter(explode(',', $request->getQueryParameter($filterName)));
            return $carry;
        }, []);
    }

    /**
     * @param HttpRequest $request
     * @return ProductsPerPage
     */
    public function getProductsPerPage(HttpRequest $request)
    {
        $productsPerPageQueryStringValue = $this->getProductsPerPageQueryStringValue($request);
        if (null !== $productsPerPageQueryStringValue) {
            $numbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            return ProductsPerPage::create($numbersOfProductsPerPage, (int) $productsPerPageQueryStringValue);
        }

        if ($request->hasCookie(self::PRODUCTS_PER_PAGE_COOKIE_NAME)) {
            $numbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            $selected = (int) $request->getCookieValue(self::PRODUCTS_PER_PAGE_COOKIE_NAME);
            return ProductsPerPage::create($numbersOfProductsPerPage, $selected);
        }

        return $this->productsPerPage;
    }

    /**
     * @param HttpRequest $request
     * @return SortOrderConfig
     */
    public function getSelectedSortOrderConfig(HttpRequest $request)
    {
        $sortOrderQueryStringValue = $this->getSortOrderQueryStringValue($request);
        $sortDirectionQueryStringValue = $this->getSortDirectionQueryStringValue($request);

        if ($sortOrderQueryStringValue !== null && $sortDirectionQueryStringValue !== null) {
            $sortOrderDirection = SortOrderDirection::create($sortDirectionQueryStringValue);
            return $this->createSelectedSortOrderConfig($sortOrderQueryStringValue, $sortOrderDirection);
        }

        if ($request->hasCookie(self::SORT_ORDER_COOKIE_NAME) &&
            $request->hasCookie(self::SORT_DIRECTION_COOKIE_NAME)
        ) {
            $sortOrder = $request->getCookieValue(self::SORT_ORDER_COOKIE_NAME);
            $direction = $request->getCookieValue(self::SORT_DIRECTION_COOKIE_NAME);
            $sortOrderDirection = SortOrderDirection::create($direction);

            return $this->createSelectedSortOrderConfig($sortOrder, $sortOrderDirection);
        }

        foreach ($this->sortOrderConfigs as $sortOrderConfig) {
            if ($sortOrderConfig->isSelected()) {
                return $sortOrderConfig;
            }
        }

        throw new NoSelectedSortOrderException('No selected sort order config is found.');
    }

    public function processCookies(HttpRequest $request)
    {
        $productsPerPage = $this->getProductsPerPageQueryStringValue($request);
        if ($productsPerPage !== null) {
            setcookie(
                self::PRODUCTS_PER_PAGE_COOKIE_NAME,
                $productsPerPage,
                time() + self::PRODUCTS_PER_PAGE_COOKIE_TTL
            );
        }

        $sortOrder = $this->getSortOrderQueryStringValue($request);
        if ($sortOrder !== null) {
            setcookie(
                self::SORT_ORDER_COOKIE_NAME,
                $sortOrder,
                time() + self::SORT_ORDER_COOKIE_TTL
            );
        }

        $sortDirection = $this->getSortDirectionQueryStringValue($request);
        if ($sortDirection !== null) {
            setcookie(
                self::SORT_DIRECTION_COOKIE_NAME,
                $sortDirection,
                time() + self::SORT_DIRECTION_COOKIE_TTL
            );
        }
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getProductsPerPageQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(self::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME);
    }

    /**
     * @param string $attributeCodeString
     * @param SortOrderDirection $direction
     * @return SortOrderConfig
     */
    private function createSelectedSortOrderConfig($attributeCodeString, SortOrderDirection $direction)
    {
        $attributeCode = AttributeCode::fromString($attributeCodeString);
        return SortOrderConfig::createSelected($attributeCode, $direction);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getSortOrderQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(self::SORT_ORDER_QUERY_PARAMETER_NAME);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getSortDirectionQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(self::SORT_DIRECTION_QUERY_PARAMETER_NAME);
    }
}
