<?php

namespace Brera\Renderer;

use Brera\XPathParser;

class LayoutReader
{
    /**
     * @param string $layoutXmlFilePath
     * @return Layout
     */
    public function loadLayoutFromXmlFile($layoutXmlFilePath)
    {
        if (!is_readable($layoutXmlFilePath) || is_dir($layoutXmlFilePath)) {
            throw new LayoutFileNotReadableException();
        }

        $layoutXml = file_get_contents($layoutXmlFilePath);
        $parser = new XPathParser($layoutXml);
        $layoutArray = $parser->getXmlNodesArrayByXPath('/*');

        return Layout::fromArray($layoutArray);
    }
}