<?php

namespace LizardsAndPumpkins\Renderer;

use LizardsAndPumpkins\Utils\XPathParser;

class LayoutReader
{
    /**
     * @param string $layoutXmlFilePath
     * @return Layout
     */
    public function loadLayoutFromXmlFile($layoutXmlFilePath)
    {
        chdir(__DIR__ . '/../..');

        if (!is_readable($layoutXmlFilePath) || is_dir($layoutXmlFilePath)) {
            throw new LayoutFileNotReadableException(sprintf(
                'The layout file "%s" is not readable.',
                $layoutXmlFilePath
            ));
        }

        $layoutXml = file_get_contents($layoutXmlFilePath);
        $parser = new XPathParser($layoutXml);
        $layoutArray = $parser->getXmlNodesArrayByXPath('/*');

        return Layout::fromArray($layoutArray);
    }
}
