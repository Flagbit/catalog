<?php


namespace Brera\Context;

abstract class ContextDecorator implements Context
{
    /**
     * @var Context
     */
    private $component;
    
    /**
     * @var array
     */
    private $sourceData;

    public function __construct(Context $component, array $sourceData)
    {
        $this->component = $component;
        $this->sourceData = $sourceData;
    }

    /**
     * @param string $code
     * @return string
     */
    final public function getValue($code)
    {
        if ($this->getCode() === $code) {
            return $this->getValueFromContext();
        }
        return $this->component->getValue($code);
    }

    /**
     * @return string
     */
    protected function getValueFromContext()
    {
        return $this->defaultGetValueFromContextImplementation();
    }

    /**
     * @return string
     */
    private function defaultGetValueFromContextImplementation()
    {
        if (! array_key_exists($this->getCode(), $this->sourceData)) {
            throw new ContextCodeNotFoundException(sprintf(
                'No value found in the context source data for the code "%s"',
                $this->getCode()
            ));
        }
        return $this->sourceData[$this->getCode()];
    }

    /**
     * @return string
     */
    abstract protected function getCode();

    /**
     * @return string[]
     */
    final public function getSupportedCodes()
    {
        return array_merge([$this->getCode()], $this->component->getSupportedCodes());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getCode() . ':' . $this->getValueFromContext() . '_' . $this->component->getId();
    }

    /**
     * @return mixed[]
     */
    final protected function getSourceData()
    {
        return $this->sourceData;
    }
}
