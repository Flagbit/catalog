<?php

namespace Brera\PoC;

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

            if (substr($name, 0, 6) != 'create' && substr($name, 0, 3) != 'get') {
                continue;
            }
            
            $this->methods[$name] = $factory;
        }

        $factory->setMasterFactory($this);
    }

    final public function __call($method, $parameters)
    {
        if (!isset($this->methods[$method])) {
            throw new UndefinedFactoryMethodException(sprintf('Unknown method "%s"', $method));
        }

        return call_user_func_array(array($this->methods[$method], $method), $parameters);
    }
}
