<?php


namespace Brera\Environment;

use Brera\InputXmlIsEmptyStringException;
use Brera\InvalidXmlTypeException;
use Brera\XPathParser;

class EnvironmentSourceBuilder
{

    /**
     * @var EnvironmentBuilder
     */
    private $environmentBuilder;

    public function __construct(EnvironmentBuilder $environmentBuilder)
    {
        $this->environmentBuilder = $environmentBuilder;
    }
    
    /**
     * @param string $xml
     * @return EnvironmentSource
     */
    public function createFromXml($xml)
    {
        $this->validateXmlString($xml);
        $environments = $this->extractAttributesFromXml($xml);
        return new EnvironmentSource($environments, $this->environmentBuilder);
    }

    /**
     * @param string $xml
     * @throws InvalidXmlTypeException
     * @throws InputXmlIsEmptyStringException
     */
    private function validateXmlString($xml)
    {
        if (!is_string($xml)) {
            throw new InvalidXmlTypeException('The XML data has to be passed as a string');
        }
        if (empty($xml)) {
            throw new InputXmlIsEmptyStringException('The input XML data is empty.');
        }
    }

    /**
     * @param string $xml
     * @return string[]
     */
    private function extractAttributesFromXml($xml)
    {
        $environments = [];
        $parser = new XPathParser($xml);

        $attributes = $parser->getXmlNodesArrayByXPath('//product/attributes//@*');
        foreach ($attributes as $attribute) {
            $environments[$attribute['nodeName']][] = $attribute['value'];
        }

        return $environments;
    }
}
