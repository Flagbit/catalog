<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var string[]
     */
    private $indexAttributeCodes;

    /**
     * @param string[] $indexAttributeCodes
     */
    public function __construct(array $indexAttributeCodes)
    {
        $this->indexAttributeCodes = $indexAttributeCodes;
    }

    /**
     * @param mixed $projectionSourceData
     * @return SearchDocumentCollection
     */
    public function aggregate($projectionSourceData)
    {
        if (!($projectionSourceData instanceof Product)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        $searchDocuments = $this->createSearchDocument($projectionSourceData);

        return new SearchDocumentCollection($searchDocuments);
    }

    /**
     * @param Product $product
     * @return SearchDocument[]
     */
    private function createSearchDocument(Product $product)
    {
        $fieldsCollection = $this->createSearchDocumentFieldsCollection($product);

        return new SearchDocument($fieldsCollection, $product->getContext(), $product->getId());
    }

    /**
     * @param Product $product
     * @return SearchDocumentFieldCollection
     */
    private function createSearchDocumentFieldsCollection(Product $product)
    {
        $attributesMap = array_reduce($this->indexAttributeCodes, function ($carry, $attributeCode) use ($product) {
            $codeAndValues = [$attributeCode => $this->getAttributeValuesForSearchDocument($product, $attributeCode)];
            return array_merge($carry, $codeAndValues);
        }, []);

        return SearchDocumentFieldCollection::fromArray($attributesMap);
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     * @return array[]
     */
    private function getAttributeValuesForSearchDocument(Product $product, $attributeCode)
    {
        return array_filter($product->getAllValuesOfAttribute($attributeCode), function ($value) {
            return is_scalar($value);
        });
    }
}
