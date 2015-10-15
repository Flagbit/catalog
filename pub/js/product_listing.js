require(
    ['lib/domReady', 'product_grid', 'filter_navigation', 'pagination', 'common'],
    function (domReady, productGrid, filterNavigation, pagination) {
        domReady(function () {
            productGrid.renderGrid(productListingJson, '#products-grid-container');
            filterNavigation.generateLayeredNavigation(filterNavigationJson, '#filter-navigation');
            pagination.generatePagination(totalNumberOfResults, productsPerPage, '#pagination');
            setTotalNumberOfProductsInSelection(totalNumberOfResults, '.toolbar .amount')
        });

        function setTotalNumberOfProductsInSelection(totalNumberOfResults, selector) {
            Array.prototype.map.call(document.querySelectorAll(selector), function (targetElement) {
                var textNode = document.createTextNode(totalNumberOfResults + ' Items');
                targetElement.appendChild(textNode);
            });
        }
    }
);
