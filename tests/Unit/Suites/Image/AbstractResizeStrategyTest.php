<?php

namespace Brera\Image;

abstract class AbstractResizeStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImplementImageProcessorStrategyInterface()
    {
        $class = $this->getResizeClassName();
        $strategy = new $class(1, 1);
        $this->assertInstanceOf(ImageProcessingStrategy::class, $strategy);
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image width, got string.
     */
    public function itShouldFailIfWidthIsNotAnInteger()
    {
        $class = $this->getResizeClassName();
        (new $class('foo', 1))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Image width should be greater then zero, got 0.
     */
    public function itShouldFailIfWidthIsNotPositive()
    {
        $class = $this->getResizeClassName();
        (new $class(0, 1))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image height, got string.
     */
    public function itShouldFailIfHeightIsNotAnInteger()
    {
        $class = $this->getResizeClassName();
        (new $class(1, 'foo'))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Image height should be greater then zero, got -1.
     */
    public function itShouldFailIfHeightIsNotPositive()
    {
        $class = $this->getResizeClassName();
        (new $class(1, -1))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidBinaryImageDataException
     */
    public function itShouldFailIfImageStreamIsNotValid()
    {
        $class = $this->getResizeClassName();
        (new $class(1, 1))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidBinaryImageDataException
     */
    public function itShouldFailIfImageFormatIsNotSupported()
    {
        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/blank.ico');

        $class = $this->getResizeClassName();
        (new $class(1, 1))->processBinaryImageData($imageStream);
    }

    /**
     * @test
     */
    public function itShouldResizeImageToGivenDimensions()
    {
        $requiredImageWidth = 15;
        $requiredImageHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/test_image2.jpg');

        $class = $this->getResizeClassName();
        $result = (new $class($requiredImageWidth, $requiredImageHeight))->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($requiredImageWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredImageHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }

    /**
     * @return string
     */
    abstract protected function getResizeClassName();
}
