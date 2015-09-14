<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;

class GenericSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @var string
     */
    private $snippetCode;
    
    /**
     * @var string[]
     */
    private $contextParts;

    /**
     * @var string[]
     */
    private $usedDataParts;

    /**
     * @param string $snippetCode
     * @param string[] $contextParts
     * @param string[] $usedDataParts
     */
    public function __construct($snippetCode, array $contextParts, array $usedDataParts)
    {
        if (!is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(
                sprintf('The snippet code has to be a string, got "%s"', gettype($snippetCode))
            );
        }

        $this->snippetCode = $snippetCode;
        $this->contextParts = $contextParts;
        $this->usedDataParts = $usedDataParts;
    }

    /**
     * @param Context $context
     * @param mixed[] $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data)
    {
        $snippetKeyData = $this->getSnippetKeyDataAsString($data);
        $snippetKey = $this->snippetCode . $snippetKeyData . '_' . $context->getIdForParts($this->contextParts);

        return $snippetKey;
    }

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey()
    {
        return $this->contextParts;
    }

    /**
     * @param string[] $data
     * @return string
     */
    private function getSnippetKeyDataAsString(array $data)
    {
        $dataString = '';

        foreach ($this->usedDataParts as $dataKey) {
            if (!isset($data[$dataKey])) {
                throw new MissingSnippetKeyGenerationDataException(
                    sprintf('"%s" is missing in snippet generation data.', $dataKey)
                );
            }

            $dataString .= '_' . $data[$dataKey];
        }

        return $dataString;
    }
}
