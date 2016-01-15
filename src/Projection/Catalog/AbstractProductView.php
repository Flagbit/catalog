<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductImage\ProductImage;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Utils\ImageStorage\Image;

abstract class AbstractProductView implements ProductView
{
    /**
     * @return ProductImageFileLocator
     */
    abstract protected function getProductImageFileLocator();

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getOriginalProduct()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        return $this->getOriginalProduct()->getFirstValueOfAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        return $this->getOriginalProduct()->getAllValuesOfAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($attributeCode)
    {
        return $this->getOriginalProduct()->hasAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->getOriginalProduct()->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->getOriginalProduct()->getContext();
    }

    /**
     * @param ProductImage $productImage
     * @param string $variation
     * @return Image
     */
    private function convertImage(ProductImage $productImage, $variation)
    {
        return $this->getProductImageFileLocator()->get(
            $productImage->getFileName(),
            $variation,
            $this->getContext()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getImages($variantCode)
    {
        return array_map(function (ProductImage $productImage) use ($variantCode) {
            return $this->convertImage($productImage, $variantCode);
        }, iterator_to_array($this->getOriginalProduct()->getImages()));
    }

    /**
     * {@inheritdoc}
     */
    public function getImageCount()
    {
        return $this->getOriginalProduct()->getImageCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getImageByNumber($imageNumber, $variantCode)
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImage($variantCode) :
            $this->convertImage($this->getOriginalProduct()->getImageByNumber($imageNumber), $variantCode);
    }

    /**
     * @param int $imageNumber
     * @param string $variantCode
     * @return HttpUrl
     */
    public function getImageUrlByNumber($imageNumber, $variantCode)
    {
        return $this->getImageByNumber($imageNumber, $variantCode)->getUrl($this->getContext());
    }

    /**
     * {@inheritdoc}
     */
    public function getImageLabelByNumber($imageNumber)
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImageLabel() :
            $this->getOriginalProduct()->getImageLabelByNumber($imageNumber);
    }

    /**
     * @param string $variantCode
     * @return HttpUrl
     */
    public function getMainImageUrl($variantCode)
    {
        return $this->getImageCount() === 0 ?
            $this->getPlaceholderImageUrl($variantCode) :
            $this->getImageUrlByNumber(0, $variantCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getMainImageLabel()
    {
        return $this->getImageCount() === 0 ?
            $this->getPlaceholderImageLabel() :
            $this->getOriginalProduct()->getMainImageLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $original = json_decode(json_encode($this->getOriginalProduct()), true);
        return $this->transformProductJson($original);
    }

    /**
     * @param mixed[] $productData
     * @return mixed[]
     */
    protected function transformProductJson(array $productData)
    {
        return array_reduce(array_keys($productData), function (array $carry, $key) use ($productData) {
            switch ($key) {
                case SimpleProduct::CONTEXT:
                    $result = [];
                    break;

                case 'attributes':
                    $result = [$key => $this->transformAttributes($productData[$key])];
                    break;
                
                case 'images':
                    $result = ['images' => $this->getAllProductImageUrls()];
                    break;

                default:
                    $result = [$key => $productData[$key]];
                    break;
            }
            return array_merge($carry, $result);
        }, []);
    }

    /**
     * @param array[] $attributes
     * @return array[]
     */
    private function transformAttributes(array $attributes)
    {
        return array_reduce($attributes, function (array $carry, array $attribute) {
            $code = $attribute[ProductAttribute::CODE];
            return array_merge($carry, [$code => $this->getAttributeValue($attribute, $carry)]);
        }, []);
    }

    /**
     * @param mixed[] $attribute
     * @param string[] $carry
     * @return string|string[]
     */
    private function getAttributeValue(array $attribute, array $carry)
    {
        $code = $attribute[ProductAttribute::CODE];
        return array_key_exists($code, $carry) ?
            $this->getAttributeValuesAsArray($attribute, $carry[$code]) :
            $attribute[ProductAttribute::VALUE];
    }

    /**
     * @param mixed[] $attribute
     * @param string|string[] $existing
     * @return string[]
     */
    private function getAttributeValuesAsArray(array $attribute, $existing)
    {
        $existingValues = is_array($existing) ?
            $existing :
            [$existing];
        return array_merge($existingValues, [$attribute[ProductAttribute::VALUE]]);
    }

    /**
     * @param string $variantCode
     * @return Image
     */
    protected function getPlaceholderImage($variantCode)
    {
        return $this->getProductImageFileLocator()->getPlaceholder($variantCode, $this->getContext());
    }

    /**
     * @param string $variantCode
     * @return HttpUrl
     */
    protected function getPlaceholderImageUrl($variantCode)
    {
        return $this->getPlaceholderImage($variantCode)->getUrl($this->getContext());
    }

    /**
     * @return string
     */
    protected function getPlaceholderImageLabel()
    {
        return '';
    }

    /**
     * @return array[]
     */
    final protected function getAllProductImageUrls()
    {
        $imageUrls = [];
        foreach ($this->getProductImageFileLocator()->getVariantCodes() as $variantCode) {
            $imageUrls[$variantCode] = $this->getProductImagesAsImageArray($variantCode);

            if (count($imageUrls[$variantCode]) === 0) {
                $imageUrls[$variantCode][] = $this->getPlaceholderImageArray($variantCode);
            }
        };
        return $imageUrls;
    }

    /**
     * @param string $variantCode
     * @return array[]
     */
    private function getProductImagesAsImageArray($variantCode)
    {
        return array_map(function (ProductImage $productImage) use ($variantCode) {
            return $this->imageToArray($this->convertImage($productImage, $variantCode), $productImage->getLabel());
        }, iterator_to_array($this->getOriginalProduct()->getImages()));
    }

    /**
     * @param string $variantCode
     * @return string[]
     */
    private function getPlaceholderImageArray($variantCode)
    {
        $placeholder = $this->getProductImageFileLocator()->getPlaceholder($variantCode, $this->getContext());
        return $this->imageToArray($placeholder, '');
    }

    /**
     * @param Image $image
     * @param string $label
     * @return string[]
     */
    private function imageToArray(Image $image, $label)
    {
        return ['url' => (string) $image->getUrl($this->getContext()), 'label' => $label];
    }
}
