<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\LogMessage;
use LizardsAndPumpkins\Queue\Queue;

abstract class AbstractIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;
    
    /**
     * @param HttpRequest $request
     * @return SampleMasterFactory
     */
    final protected function prepareIntegrationTestMasterFactoryForRequest(HttpRequest $request)
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $this->registerIntegrationTestFactory($factory);
        $factory->register(new FrontendFactory($request));
        return $factory;
    }

    final protected function failIfMessagesWhereLogged(Logger $logger)
    {
        $messages = $logger->getMessages();

        if (!empty($messages)) {
            $failMessages = array_map(function (LogMessage $logMessage) {
                $messageContext = $logMessage->getContext();
                if (isset($messageContext['exception'])) {
                    /** @var \Exception $exception */
                    $exception = $messageContext['exception'];
                    return (string) $logMessage . ' ' . $exception->getFile() . ':' . $exception->getLine();
                }
                return (string) $logMessage;
            }, $messages);
            $fainMessageString = implode(PHP_EOL, $failMessages);

            $this->fail($fainMessageString);
        }
    }

    /**
     * @param MasterFactory $masterFactory
     * @return IntegrationTestFactory
     */
    private function registerIntegrationTestFactory(MasterFactory $masterFactory)
    {
        $factory = new IntegrationTestFactory($masterFactory);
        if ($this->isFirstInstantiationOfFactory()) {
            $this->storeInMemoryObjects($factory);
        } else {
            $this->persistInMemoryObjectsOnFactory($factory);
        }
        return $factory;
    }

    /**
     * @return bool
     */
    private function isFirstInstantiationOfFactory()
    {
        return null === $this->keyValueStore;
    }
    
    private function storeInMemoryObjects(IntegrationTestFactory $factory)
    {
        $this->keyValueStore = $factory->getKeyValueStore();
        $this->eventQueue = $factory->getEventQueue();
        $this->commandQueue = $factory->getCommandQueue();
        $this->searchEngine = $factory->getSearchEngine();
        $this->urlKeyStore = $factory->getUrlKeyStore();
    }

    private function persistInMemoryObjectsOnFactory(IntegrationTestFactory $factory)
    {
        $factory->setKeyValueStore($this->keyValueStore);
        $factory->setEventQueue($this->eventQueue);
        $factory->setCommandQueue($this->commandQueue);
        $factory->setSearchEngine($this->searchEngine);
        $factory->setUrlKeyStore($this->urlKeyStore);
    }
}
