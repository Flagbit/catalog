require([
    'lib/domReady',
    'common',
    'recently_viewed_products',
    'lib/styleselect',
    'lib/zoom',
    'lib/swiping_container'
], function(domReady, common, recentlyViewedProducts, styleSelect, zoom, toggleSwipingArrows) {

    var tabletWidth = 768,
        siteFullWidth = 975,
        maxQty = 5,
        selectBoxIdPrefix = 'variation_';

    domReady(function() {
        handleRecentlyViewedProducts();
        showNextSelectBox();

        adjustToPageWidth();
        window.addEventListener('resize', adjustToPageWidth);
        window.addEventListener('orientationchange', adjustToPageWidth);

        initializeZoom();
        initializeTabs();

        require([
            '//connect.facebook.net/de_DE/all.js#xfbml=1&status=0',
            '//platform.twitter.com/widgets.js',
            '//apis.google.com/js/plusone.js'
        ]);
    });

    function deleteAllSelectBoxesAfter(previousBoxAttribute) {
        var attributeCodes = variation_attributes.slice(variation_attributes.indexOf(previousBoxAttribute) + 1);
        attributeCodes.push('qty');

        attributeCodes.map(function (code) {
            var selectBoxToDelete = document.getElementById(selectBoxIdPrefix + code);
            if (null !== selectBoxToDelete) {
                var styledSelectUuid = selectBoxToDelete.getAttribute('data-ss-uuid'),
                    styledSelect = document.querySelector('div[data-ss-uuid="' + styledSelectUuid + '"]');
                selectBoxToDelete.parentNode.removeChild(selectBoxToDelete);
                styledSelect.parentNode.removeChild(styledSelect);
            }
        });
    }

    function getSelectedVariationValues() {
        return variation_attributes.reduce(function(carry, attributeCode) {
            var selectBox = document.getElementById(selectBoxIdPrefix + attributeCode);
            if (null !== selectBox) {
                carry[attributeCode] =  selectBox.value;
            }
            return carry;
        }, {});
    }

    function getAssociatedProductsMatchingSelection() {
        var selectedAttributes = getSelectedVariationValues();

        return associated_products.filter(function (product) {
            return Object.keys(product['attributes']).reduce(function (carry, attributeCode) {
                if (false === carry) {
                    return carry;
                }
                return Object.keys(selectedAttributes).reduce(function (carry, selectedAttributeCode) {
                    if (false === carry || selectedAttributeCode !== attributeCode) {
                        return carry;
                    }
                    return selectedAttributes[selectedAttributeCode] === product['attributes'][attributeCode];
                }, carry);
            }, true);
        });
    }

    function isConfigurableProduct() {
        return (typeof variation_attributes === 'object') &&
            (typeof associated_products === 'object') &&
            (variation_attributes.length > 0);
    }

    function showNextSelectBox(previousBoxAttribute) {
        var selectContainer = document.querySelector('.selects'),
            addToCartButton = document.querySelector('.product-controls button'),
            productIdField = document.querySelector('input[name="product"]');

        if (!isConfigurableProduct()) {
            selectContainer.appendChild(createQtySelectBox(maxQty));
            styleSelect('#' + selectBoxIdPrefix + 'qty');
            addToCartButton.disabled = '';
            return;
        }

        productIdField.value = '';
        addToCartButton.disabled = 'disabled';

        if (previousBoxAttribute) {
            deleteAllSelectBoxesAfter(previousBoxAttribute);
            if ('' === document.getElementById(selectBoxIdPrefix + previousBoxAttribute).value) {
                return;
            }
        }

        var matchingProducts = getAssociatedProductsMatchingSelection(),
            variationAttributeCode = variation_attributes[variation_attributes.indexOf(previousBoxAttribute) + 1];

        if (typeof variationAttributeCode === 'undefined') {
            var selectedProductStock = matchingProducts[0]['attributes']['stock_qty'];
            productIdField.value = matchingProducts[0]['product_id'];
            selectContainer.appendChild(createQtySelectBox(selectedProductStock));
            styleSelect('#' + selectBoxIdPrefix + 'qty');
            addToCartButton.disabled = '';
            return;
        }

        var options = getVariationAttributeOptionValuesArray(matchingProducts, variationAttributeCode);

        selectContainer.appendChild(createSelect(variationAttributeCode, options));
        styleSelect('#' + selectBoxIdPrefix + variationAttributeCode);
    }

    function createQtySelectBox(limit) {
        var numberOfItemsToShow = Math.min(limit, maxQty),
            select = document.createElement('SELECT');

        select.id = selectBoxIdPrefix + 'qty';

        for (var i = 1; i <= numberOfItemsToShow; i++) {
            var option = document.createElement('OPTION');
            option.textContent = i;
            option.value = i;
            select.appendChild(option);
        }

        return select;
    }

    function getVariationAttributeOptionValuesArray(products, attributeCode) {
        return products.reduce(function (carry, associatedProduct) {
            var optionIsAlreadyPresent = false;

            for (var i=0; i<carry.length; i++) {
                if (carry[i]['value'] === associatedProduct['attributes'][attributeCode]) {
                    optionIsAlreadyPresent = true;

                    if (true === carry[i]['disabled'] && associatedProduct['attributes']['stock_qty'] > 0) {
                        carry[i]['disabled'] = false;
                    }
                }
            }

            if (false === optionIsAlreadyPresent) {
                carry.push({
                    'value': associatedProduct['attributes'][attributeCode],
                    'disabled': 0 == associatedProduct['attributes']['stock_qty']
                });
            }

            return carry;
        }, []);
    }

    function createSelect(name, options) {
        var variationSelect = document.createElement('SELECT');
        variationSelect.id = selectBoxIdPrefix + name;
        variationSelect.addEventListener('change', function () { showNextSelectBox(name); }, true);

        var defaultOption = document.createElement('OPTION');
        defaultOption.textContent = 'Select ' + name;
        variationSelect.appendChild(defaultOption);

        options.map(function (option) {
            variationSelect.appendChild(createSelectOption(option));
        });

        return variationSelect;
    }

    function createSelectOption(option) {
        var variationOption = document.createElement('OPTION');
        variationOption.textContent = option['value'];
        variationOption.value = option['value'];

        if (option['disabled']) {
            variationOption.disabled = 'disabled';
        }

        return variationOption;
    }

    function handleRecentlyViewedProducts() {
        var recentlyViewedProductsListHtml = recentlyViewedProducts.getRecentlyViewedProductsHtml(product);

        if (recentlyViewedProductsListHtml.indexOf('</li>') !== -1) {
            var container = document.querySelector('#recently-viewed-products .swipe-container');
            container.innerHTML = recentlyViewedProductsListHtml;
            container.parentNode.style.display = 'block';
        }

        recentlyViewedProducts.addProductIntoLocalStorage(product);
    }

    function initializeZoom() {
        new zoom(document.querySelector('.main-image-area'));
    }

    function initializeTabs() {
        var activeTab,
            activeTabContent;

        Array.prototype.map.call(document.querySelectorAll('ul.tabs a'), function (tabLink, index) {
            if (0 === index) {
                activeTab = tabLink;
                activeTab.className = 'active';

                activeTabContent = document.getElementById(activeTab.hash.slice(1));
                activeTabContent.style.display = 'block';
            }

            tabLink.addEventListener('click', function (event) {
                event.preventDefault();

                activeTab.className = '';
                activeTabContent.style.display = 'none';

                activeTab = event.target;
                activeTab.className = 'active';

                activeTabContent = document.getElementById(activeTab.hash.slice(1));
                activeTabContent.style.display = 'block';
            }, true);
        });
    }

    function adjustToPageWidth() {
        var currentWidth = document.body.clientWidth,
        /* Maybe it makes sense to initialize variables on load only ? */
            productTitle = document.querySelector('.product-essential h1'),
            brandLogo = document.getElementById('brandLogo'),
            socialIcons = document.querySelector('.socialSharing'),
            productTopContainer = document.querySelector('.product-shop > .top'),
            productControls = document.querySelector('.product-controls'),
            price = document.querySelector('.price-information'),
            productMainInfo = document.querySelector('.product-main-info'),
            similarProductsLink = document.querySelector('.similarProducts'),
            articleInformation = document.querySelector('.articleInformations');

        /* Phone only */
        if (currentWidth < tabletWidth) {
            var phoneTitlePlaceholder = document.getElementById('phoneTitlePlaceholder');

            if (!isParent(phoneTitlePlaceholder, productTitle)) {
                phoneTitlePlaceholder.appendChild(productTitle);
            }

            if (!isParent(phoneTitlePlaceholder, brandLogo)) {
                phoneTitlePlaceholder.appendChild(brandLogo);
            }

            if (!isParent(productTopContainer, price)) {
                productTopContainer.appendChild(price);
            }

            if (!isParent(productControls, similarProductsLink)) {
                productControls.appendChild(similarProductsLink);
            }

            if (!isParent(productControls, articleInformation)) {
                productControls.appendChild(articleInformation);
            }

            if (!isParent(productControls, socialIcons)) {
                productControls.appendChild(socialIcons);
            }

            /* TODO: Implement image slider */

            /* Hide "send" part of FB buttons block if not yet hidden */
            fbEnsureInit(processFbButton);
        } else {
            var originalTitleContainer = document.querySelector('.product-title');

            if (!isParent(originalTitleContainer, brandLogo)) {
                originalTitleContainer.appendChild(brandLogo);
            }

            if (!isParent(originalTitleContainer, productTitle)) {
                originalTitleContainer.appendChild(productTitle);
            }

            if (!isParent(originalTitleContainer, similarProductsLink)) {
                originalTitleContainer.appendChild(similarProductsLink);
            }

            if (!isParent(productTopContainer, articleInformation)) {
                productTopContainer.appendChild(articleInformation);
            }

            if (!isParent(productTopContainer, socialIcons)) {
                productTopContainer.appendChild(socialIcons);
            }

            if (!isParent(productMainInfo, price)) {
                productMainInfo.appendChild(price);
            }

            /* Revert "send" part of FB buttons block if not yet recovered */
            fbEnsureInit(processFbButton);
        }

        /* Tablet only */
        if (currentWidth < siteFullWidth && currentWidth >= tabletWidth) {

            if (!isParent(productTopContainer, price)) {
                productTopContainer.appendChild(price);
            }

            if (!isParent(productTopContainer, articleInformation)) {
                productTopContainer.appendChild(articleInformation);
            }

        } else if (currentWidth >= siteFullWidth) {

            if (!isParent(productMainInfo, price)) {
                productMainInfo.appendChild(price);
            }

            if (!isParent(productTopContainer, articleInformation)) {
                productTopContainer.appendChild(articleInformation);
            }
        }

        toggleSwipingArrows('.swipe-container', 'ul');
    }

    function isParent(parent, child) {
        var node = child.parentNode;
        while (node != null) {
            if (node == parent) {
                return true;
            }
            node = node.parentNode;
        }
        return false;
    }

    /**
     * Wrapper for a FB calls. Makes sure FB.init() was already executed, otherwise will wait until it is.
     */
    function fbEnsureInit(callback) {
        if (typeof FB == 'undefined') {
            setTimeout(function () { fbEnsureInit(callback) }, 50);
        } else if (callback) {
            callback();
        }
    }

    function processFbButton() {
        var fbContainer = document.querySelector('.fb-like');

        if (document.body.clientWidth < tabletWidth) {
            if (fbContainer.getAttribute('data-send')) {
                fbContainer.removeAttribute('data-send');
                FB.XFBML.parse();
            }
        } else {
            if (typeof fbContainer == 'undefined' || typeof fbContainer.getAttribute('data-send') == 'undefined') {
                fbContainer.setAttribute('data-send', true);
                FB.XFBML.parse();
            }
        }
    }
});
