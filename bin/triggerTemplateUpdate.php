#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Projection\LoggingCommandHandlerFactory;
use LizardsAndPumpkins\Projection\LoggingDomainEventHandlerFactory;
use LizardsAndPumpkins\Projection\TemplateProjectorLocator;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\BaseCliCommand;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

class TriggerTemplateUpdate extends BaseCliCommand
{
    /**
     * @var MasterFactory
     */
    private $factory;

    private function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }

    /**
     * @return RunImport
     */
    public static function bootstrap()
    {
        $factory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $implementationFactory = new TwentyOneRunFactory();
        $factory->register($commonFactory);
        $factory->register($implementationFactory);
        //self::enableDebugLogging($factory, $commonFactory, $implementationFactory);

        return new self($factory, new CLImate());
    }

    private static function enableDebugLogging(
        MasterFactory $factory,
        CommonFactory $commonFactory,
        TwentyOneRunFactory $implementationFactory
    ) {
        $factory->register(new LoggingDomainEventHandlerFactory($commonFactory));
        $factory->register(new LoggingCommandHandlerFactory($commonFactory));
        $factory->register(new LoggingQueueFactory($implementationFactory));
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate)
    {
        return array_merge(parent::getCommandLineArgumentsArray($climate), [
            'processQueues' => [
                'prefix'      => 'p',
                'longPrefix'  => 'processQueues',
                'description' => 'Process queues',
                'noValue'     => true,
            ],
            'templateId'    => [
                'description' => 'Template ID',
                'required'    => true,
            ],
        ]);
    }

    protected function execute(CLImate $CLImate)
    {
        $this->addDomainEvent();
        $this->processQueuesIfRequested();
    }

    private function addDomainEvent()
    {
        $templateId = $this->getTemplateIdToProject();
        $projectionSourceData = '';

        $this->factory->getEventQueue()->add(new TemplateWasUpdatedDomainEvent($templateId, $projectionSourceData));
    }

    private function processQueuesIfRequested()
    {
        if ($this->getArg('processQueues')) {
            $this->processQueues();
        }
    }

    private function processQueues()
    {
        $this->processCommandQueue();
        $this->processDomainEventQueue();
    }

    private function processCommandQueue()
    {
        $this->output('Processing command queue...');
        $this->processQueueWhileMessagesPending(
            $this->factory->getCommandQueue(),
            $this->factory->createCommandConsumer()
        );
    }

    private function processDomainEventQueue()
    {
        $this->output('Processing domain event queue...');
        $this->processQueueWhileMessagesPending(
            $this->factory->getEventQueue(),
            $this->factory->createDomainEventConsumer()
        );
    }

    private function processQueueWhileMessagesPending(Queue $queue, QueueMessageConsumer $consumer)
    {
        while ($queue->count()) {
            $consumer->process();
        }
    }

    /**
     * @return bool|float|int|null|string
     */
    private function getTemplateIdToProject()
    {
        $templateId = $this->getArg('templateId');
        if (!in_array($templateId, $this->getValidTemplateIds())) {
            $message = $this->getInvalidTemplateIdMessage($templateId);
            throw new \InvalidArgumentException($message);
        }
        return $templateId;
    }

    /**
     * @param string $templateId
     * @return string
     */
    private function getInvalidTemplateIdMessage($templateId)
    {
        return sprintf(
            'Invalid template ID "%s". Valid template IDs are: %s',
            $templateId,
            implode(', ', $this->getValidTemplateIds())
        );
    }

    /**
     * @return string[]
     */
    private function getValidTemplateIds()
    {
        /** @var TemplateProjectorLocator $templateProjectorLocator */
        $templateProjectorLocator = $this->factory->createTemplateProjectorLocator();
        return $templateProjectorLocator->getRegisteredProjectorCodes();
    }
}

TriggerTemplateUpdate::bootstrap()->run();
