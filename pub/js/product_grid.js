define(function () {

    var wrapIntoProductLink = function (element, url) {
        var link = document.createElement('A');
        link.href = url;
        link.appendChild(element);
        return link;
    };

    var createProductImage = function (imageUrl, alt) {
        var image = new Image();
        image.src = imageUrl;
        image.alt = alt;
        return image;
    };

    var getBrandLogoSrc = function (brandName) {
        var brand = brandName.toString().toLocaleLowerCase().replace(/\W/, '_');
        return baseUrl + 'images/brands/brands-slider/' + brand + '.png';
    };

    var turnIntoStringIfIsArray = function (operand) {
        if (operand.isArray) {
            return operand.join(', ');
        }

        return operand;
    };

    return {
        renderGrid: function (productGridJson, productPrices, productGridPlaceholderSelector) {
            var productGridPlaceholder = document.querySelector(productGridPlaceholderSelector);

            if (null === productGridPlaceholder) {
                return;
            }

            var grid = document.createElement('UL');
            grid.className = 'products-grid';

            productGridJson.map(function (product, index) {
                var mainImage = product['images']['medium'][0];
                var productLi = document.createElement('LI'),
                    container = document.createElement('DIV'),
                    title = document.createElement('H2'),
                    gender = document.createElement('P'),
                    productUrl = baseUrl + product['attributes']['url_key'],
                    productImage = createProductImage(mainImage['url'], mainImage['label']),
                    price = document.createElement('SPAN'),
                    hasSpecialPrice = 2 === productPrices[index].length;

                title.textContent = product['attributes']['name'];
                gender.textContent = turnIntoStringIfIsArray(product['attributes']['gender']);

                price.textContent = productPrices[index][0];
                price.className = hasSpecialPrice ? 'old-price' : 'regular-price';

                container.style.backgroundImage = 'url("' + getBrandLogoSrc(product['attributes']['brand']) + '")';
                container.className = 'grid-cell-container';

                container.appendChild(wrapIntoProductLink(productImage, productUrl));
                container.appendChild(wrapIntoProductLink(title, productUrl));
                container.appendChild(gender);
                container.appendChild(price);

                if (hasSpecialPrice) {
                    var specialPrice = document.createElement('SPAN');
                    specialPrice.textContent = productPrices[index][1];
                    specialPrice.className = 'special-price';
                    container.appendChild(specialPrice);
                }

                productLi.appendChild(container);
                grid.appendChild(productLi);
            });

            productGridPlaceholder.appendChild(grid);
        }
    }
});
