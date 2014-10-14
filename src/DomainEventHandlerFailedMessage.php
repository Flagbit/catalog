<?php


namespace Brera\PoC;


class DomainEventHandlerFailedMessage implements LogMessage
{
    /**
     * @var DomainEvent
     */
    private $domainEvent;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(DomainEvent $domainEvent, \Exception $exception)
    {
        $this->domainEvent = $domainEvent;
        $this->exception = $exception;
    }

    /**
     * @return DomainEvent
     */
    public function getDomainEvent()
    {
        return $this->domainEvent;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
} 