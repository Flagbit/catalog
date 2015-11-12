<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\SnippetCodeCanNotBeProcessedException;

class RegistrySnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocatorStrategy
{
    /**
     * @var \Closure[]
     */
    private $closures = [];

    /**
     * {@inheritdoc}
     */
    public function canHandle($snippetCode)
    {
        return array_key_exists($snippetCode, $this->closures);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
    {
        $this->validateSnippetCode($snippetCode);

        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->closures[$snippetCode]);
    }

    /**
     * @param string $snippetCode
     */
    private function validateSnippetCode($snippetCode)
    {
        if (!is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(sprintf(
                'Expected snippet code to be a string but got "%s"',
                (is_scalar($snippetCode) ? $snippetCode : gettype($snippetCode))
            ));
        }
    }

    /**
     * @param string $snippetCode
     * @param \Closure $closure
     */
    public function register($snippetCode, \Closure $closure)
    {
        $this->validateSnippetCode($snippetCode);
        $this->closures[$snippetCode] = $closure;
    }
}
