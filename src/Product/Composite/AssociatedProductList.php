<?php


namespace LizardsAndPumpkins\Product\Composite;

use LizardsAndPumpkins\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Product\Composite\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Product\Product;

class AssociatedProductList implements \JsonSerializable, \IteratorAggregate
{
    /**
     * @var Product[]
     */
    private $products;

    public function __construct(Product ...$products)
    {
        $this->validateAssociatedProducts(...$products);
        $this->products = $products;
    }

    private function validateAssociatedProducts(Product ...$products)
    {
        array_reduce($products, function (array $idStrings, Product $product) {
            $productIdString = (string) $product->getId();
            if (in_array($productIdString, $idStrings)) {
                throw $this->createDuplicateAssociatedProductException($productIdString);
            }
            return array_merge($idStrings, [$productIdString]);
        }, []);
    }

    /**
     * @param string $productIdString
     * @return DuplicateAssociatedProductException
     */
    private function createDuplicateAssociatedProductException($productIdString)
    {
        $message = sprintf('The product "%s" is associated two times to the same composite product', $productIdString);
        return new DuplicateAssociatedProductException($message);
    }

    /**
     * @param array[] $sourceArray
     * @return AssociatedProductList
     */
    public static function fromArray(array $sourceArray)
    {
        $associatedProducts = self::createAssociatedProductsFromArray($sourceArray);
        return new self(...$associatedProducts);
    }

    /**
     * @param array[] $sourceArray
     * @return Product[]
     */
    private static function createAssociatedProductsFromArray(array $sourceArray)
    {
        return array_map(function ($idx) use ($sourceArray) {
            $class = $sourceArray['product_php_classes'][$idx];
            $productSourceArray = $sourceArray['products'][$idx];
            return self::createAssociatedProductFromArray($class, $productSourceArray);
        }, array_keys($sourceArray['products']));
    }

    /**
     * @param string $class
     * @param mixed[] $productSourceArray
     * @return Product
     */
    private static function createAssociatedProductFromArray($class, array $productSourceArray)
    {
        return forward_static_call([$class, 'fromArray'], $productSourceArray);
    }

    /**
     * @return Product[]
     */
    public function jsonSerialize()
    {
        return [
            'product_php_classes' => $this->getAssociatedProductClassNames(),
            'products' => $this->getProducts()
        ];
    }

    /**
     * @return string[]
     */
    private function getAssociatedProductClassNames()
    {
        return array_map(function (Product $product) {
            return get_class($product);
        }, $this->products);
    }
    
    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->products);
    }

    /**
     * @param string ...$attributeCodes
     */
    public function validateUniqueValueCombinationForEachProductAttribute(...$attributeCodes)
    {
        $this->validateAllProductsHaveTheAttributes(...$attributeCodes);
        array_reduce($this->products, function ($carry, Product $product) use ($attributeCodes) {
            $attributeValuesForProduct = $this->getAttributeValuesForProduct($product, $attributeCodes);
            $otherProductId = array_search($attributeValuesForProduct, $carry);
            if (false !== $otherProductId) {
                throw $this->createProductAttributeValueCombinationNotUniqueException(
                    $otherProductId,
                    (string) $product->getId(),
                    ...$attributeCodes
                );
            }
            return array_merge($carry, [(string) $product->getId() => $attributeValuesForProduct]);
        }, []);
    }

    /**
     * @param Product $product
     * @param string[] $attributeCodes
     * @return array[]
     */
    private function getAttributeValuesForProduct(Product $product, array $attributeCodes)
    {
        return array_reduce($attributeCodes, function ($carry, $attributeCode) use ($product) {
            $allValuesOfAttribute = $product->getAllValuesOfAttribute($attributeCode);
            return array_merge($carry, [(string) $attributeCode => $allValuesOfAttribute]);
        }, []);
    }

    /**
     * @param string $productId1
     * @param string $productId2
     * @param string ...$attrCodes
     * @return ProductAttributeValueCombinationNotUniqueException
     */
    private function createProductAttributeValueCombinationNotUniqueException($productId1, $productId2, ...$attrCodes)
    {
        $message = sprintf(
            'The associated products "%s" and "%s" have the same value combination for the attributes "%s"',
            $productId1,
            $productId2,
            implode('" and "', $attrCodes)
        );
        return new ProductAttributeValueCombinationNotUniqueException($message);
    }

    /**
     * @param string ...$attributeCodes
     */
    private function validateAllProductsHaveTheAttributes(...$attributeCodes)
    {
        array_map(function (Product $product) use ($attributeCodes) {
            $this->validateProductHasAttributes($product, $attributeCodes);
        }, $this->products);
    }

    /**
     * @param Product $product
     * @param string[] $attributeCodes
     */
    private function validateProductHasAttributes(Product $product, array $attributeCodes)
    {
        array_map(function ($attributeCode) use ($product) {
            $this->validateProductHasAttribute($product, $attributeCode);
        }, $attributeCodes);
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     */
    private function validateProductHasAttribute(Product $product, $attributeCode)
    {
        if (!$product->hasAttribute($attributeCode)) {
            $message = sprintf(
                'The associated product "%s" is missing the required attribute "%s"',
                $product->getId(),
                $attributeCode
            );
            throw new AssociatedProductIsMissingRequiredAttributesException($message);
        }
    }
}
