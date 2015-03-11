<?php


namespace Brera\Context;

use Brera\DataVersion;
use Brera\Http\HttpRequest;

class ContextBuilder
{
    /**
     * @var string[]
     */
    private $registeredContextDecorators = [];
    /**
     * @var
     */
    private $dataVersion;

    /**
     * @param DataVersion $dataVersion
     */
    public function __construct(DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
    }

    /**
     * @param HttpRequest $request
     * @return Context
     * @todo Implement this
     */
    public function createFromRequest(HttpRequest $request)
    {
        return $this->getContext(['website' => 'ru', 'language' => 'de_DE']);
    }

    /**
     * @param string[] $contextDataSets
     * @return Context[]
     */
    public function getContexts(array $contextDataSets)
    {
        return array_map([$this, 'getContext'], $contextDataSets);
    }

    /**
     * @param array $contextDataSet
     * @return Context
     */
    public function getContext(array $contextDataSet)
    {
        $versionedContext = new VersionedContext($this->dataVersion);
        $codes = array_diff(array_keys($contextDataSet), [VersionedContext::CODE]);
        return array_reduce($codes, function ($context, $code) use ($contextDataSet) {
            return $this->createContextDecorator($context, $code, $contextDataSet);
        }, $versionedContext);
    }

    /**
     * @param string $code
     * @param string $contextDecoratorClass
     */
    public function registerContextDecorator($code, $contextDecoratorClass)
    {
        $this->validateDecoratorClass($code, $contextDecoratorClass);
        $this->registeredContextDecorators[$code] = $contextDecoratorClass;
    }

    /**
     * @param Context $context
     * @param string $code
     * @param array $contextSourceDataSet
     * @return ContextDecorator
     */
    private function createContextDecorator(Context $context, $code, array $contextSourceDataSet)
    {
        $decoratorClass = $this->getDecoratorClass($code);
        return new $decoratorClass($context, $contextSourceDataSet);
    }

    /**
     * @param string $code
     * @return string
     */
    private function getDecoratorClass($code)
    {
        return array_key_exists($code, $this->registeredContextDecorators) ?
            $this->registeredContextDecorators[$code] :
            $this->getDefaultContextDecoratorClass($code);
    }

    /**
     * @param string $code
     * @return string
     */
    private function getDefaultContextDecoratorClass($code)
    {
        $decoratorClass = ucfirst($this->removeUnderscores($code)) . 'ContextDecorator';
        $qualifiedDecoratorClass = '\\Brera\\Context\\' . $decoratorClass;
        $this->validateDecoratorClass($code, $qualifiedDecoratorClass);
        $this->registerContextDecorator($code, $qualifiedDecoratorClass);
        return $qualifiedDecoratorClass;
    }

    /**
     * @param string $code
     * @param string $qualifiedClassName
     * @throws ContextDecoratorNotFoundException
     * @throws InvalidContextDecoratorClassException
     */
    private function validateDecoratorClass($code, $qualifiedClassName)
    {
        if (!class_exists($qualifiedClassName)) {
            throw new ContextDecoratorNotFoundException(
                sprintf('Context decorator class "%s" not found for code "%s"', $qualifiedClassName, $code)
            );
        }
        if (!in_array(ContextDecorator::class, class_parents($qualifiedClassName))) {
            throw new InvalidContextDecoratorClassException(sprintf(
                'Context Decorator class "%s" does not extend \\Brera\\Context\\ContextDecorator',
                $qualifiedClassName
            ));
        }
    }

    /**
     * @param string $code
     * @return string
     */
    private function removeUnderscores($code)
    {
        return str_replace('_', '', preg_replace_callback('/_([a-z])/', function ($m) {
            return strtoupper($m[1]);
        }, $code));
    }
}
