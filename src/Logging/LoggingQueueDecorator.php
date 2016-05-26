<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

class LoggingQueueDecorator implements Queue, Clearable
{
    /**
     * @var Queue
     */
    private $component;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $component, Logger $logger)
    {
        $this->component = $component;
        $this->logger = $logger;
    }

    public function count(): int
    {
        return $this->component->count();
    }

    public function isReadyForNext(): bool
    {
        return $this->component->isReadyForNext();
    }

    public function add(Message $message)
    {
        $this->logger->log(new QueueAddLogMessage($message->getName(), $this->component));
        $this->component->add($message);
    }

    public function next(): Message
    {
        return $this->component->next();
    }

    public function clear()
    {
        if ($this->component instanceof Clearable) {
            $this->component->clear();
        }
    }
}
