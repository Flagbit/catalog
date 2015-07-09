<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageMagickResizeStrategy
 * @covers \Brera\Image\ResizeStrategyTrait
 */
class ImageMagickResizeStrategyTest extends AbstractResizeStrategyTest
{
    protected function setUp()
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('The PHP extension imagick is not installed');
        }
    }
    
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return ImageMagickResizeStrategy::class;
    }
}
