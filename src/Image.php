<?php

namespace LizardsAndPumpkins;

class Image
{
    const MEDIA_DIR = 'media/product';

    /**
     * @var string
     */
    private $src;

    /**
     * @var string
     */
    private $label;

    /**
     * @param string $src
     * @param string $label
     */
    public function __construct($src, $label = '')
    {
        $this->src = $src;
        $this->label = $label;
    }

    /**
     * @param string $size
     * @return string
     */
    public function getPath($size)
    {
        // TODO: Re-implement w/o putting project specific data (size label, media dir) into general purpose class
        // Todo: Also the data version has to be part of the path

        return '/lizards-and-pumpkins/' . self::MEDIA_DIR . '/' . $size . '/' . $this->src;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
