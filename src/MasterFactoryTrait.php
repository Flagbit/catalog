<?php

namespace Brera;

trait MasterFactoryTrait
{
    /**
     * @var array
     */
    private $methods = [];

    final public function register(Factory $factory)
    {
        foreach ((new \ReflectionObject($factory))->getMethods() as $method) {
            $name = $method->getName();

            if ($method->isProtected() || $method->isPrivate()) {
                continue;
            }

            if ('create' !== substr($name, 0, 6) && 'get' !== substr($name, 0, 3)) {
                continue;
            }

            $this->methods[$name] = $factory;
        }

        $factory->setMasterFactory($this);
    }

    /**
     * @param string $method
     * @param mixed[] $parameters
     * @return mixed
     * @throws UndefinedFactoryMethodException
     */
    final public function __call($method, array $parameters)
    {
        if (!isset($this->methods[$method])) {
            throw new UndefinedFactoryMethodException(sprintf('Unknown method "%s"', $method));
        }

        return call_user_func_array([$this->methods[$method], $method], $parameters);
    }
}
