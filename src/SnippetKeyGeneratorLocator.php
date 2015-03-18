<?php


namespace Brera;

class SnippetKeyGeneratorLocator
{
    /**
     * @var SnippetKeyGenerator[]
     */
    private $keyGenerators = [];

    /**
     * @var string[]
     * @todo Make injectable or maybe use ContextSource
     */
    private $defaultContextParts = ['website', 'language', 'version'];

    /**
     * @param string $snippetCode
     * @return GenericSnippetKeyGenerator
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
    {
        $this->validateSnippetCode($snippetCode);
        if (!array_key_exists($snippetCode, $this->keyGenerators)) {
            $this->keyGenerators[$snippetCode] = new GenericSnippetKeyGenerator(
                $snippetCode,
                $this->defaultContextParts
            );
        }

        return $this->keyGenerators[$snippetCode];
    }

    /**
     * @param string $snippetCode
     * @throws InvalidSnippetCodeException
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
     * @param SnippetKeyGenerator $snippetKeyGenerator
     */
    public function register($snippetCode, SnippetKeyGenerator $snippetKeyGenerator)
    {
        $this->validateSnippetCode($snippetCode);
        $this->keyGenerators[$snippetCode] = $snippetKeyGenerator;
    }
}
