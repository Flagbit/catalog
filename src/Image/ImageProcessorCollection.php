<?php

namespace Brera\Image;

class ImageProcessorCollection
{
    /**
     * @var ImageProcessor[]
     */
    private $processors = [];

    public function add(ImageProcessor $processor)
    {
        array_push($this->processors, $processor);
    }

    /**
     * @param string $imageFileName
     */
    public function process($imageFileName)
    {
        foreach ($this->processors as $processor) {
            $processor->process($imageFileName);
        }
    }
}
