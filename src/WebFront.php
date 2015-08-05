<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpResponse;
use Brera\Http\HttpRouterChain;

abstract class WebFront
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var HttpRouterChain
     */
    private $routerChain;

    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return HttpResponse
     */
    public function run()
    {
        $response = $this->runWithoutSendingResponse();
        $response->send();
        return $response;
    }

    /**
     * @return HttpResponse
     */
    public function runWithoutSendingResponse()
    {
        $this->buildFactory();
        $this->buildRouterChain();

        $context = $this->getMasterFactory()->getContext($this->request);
        $requestHandler = $this->routerChain->route($this->request, $context);

        // TODO put response creation into factory, response depends on http version!

        return $requestHandler->process($this->request);
    }

    final public function registerFactory(Factory $factory)
    {
        $this->buildFactory();
        $this->masterFactory->register($factory);
    }

    /**
     * @return MasterFactory
     */
    abstract protected function createMasterFactory();

    /**
     * @param MasterFactory $factory
     */
    abstract protected function registerFactories(MasterFactory $factory);

    /**
     * @param HttpRouterChain $router
     */
    abstract protected function registerRouters(HttpRouterChain $router);

    /**
     * @return HttpRequest
     */
    final protected function getRequest()
    {
        return $this->request;
    }

    private function buildFactory()
    {
        if (null !== $this->masterFactory) {
            return;
        }
        
        $this->masterFactory = $this->createMasterFactory();
        $this->validateMasterFactory();
        $this->registerFactories($this->masterFactory);
    }

    private function buildRouterChain()
    {
        $this->routerChain = $this->masterFactory->createHttpRouterChain();
        $this->registerRouters($this->routerChain);
    }

    /**
     * @return MasterFactory
     */
    public function getMasterFactory()
    {
        $this->buildFactory();
        return $this->masterFactory;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validateMasterFactory()
    {
        if (!($this->masterFactory instanceof MasterFactory)) {
            throw new \InvalidArgumentException(sprintf(
                'Factory is not of type MasterFactory but "%s"',
                $this->getExceptionMessageClassNameRepresentation($this->masterFactory)
            ));
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function getExceptionMessageClassNameRepresentation($value)
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_null($value)) {
            return 'NULL';
        }

        return (string) $value;
    }
}
