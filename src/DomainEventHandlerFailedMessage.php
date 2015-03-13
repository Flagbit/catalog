<?php

namespace Brera;

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

    /**
     * @param DomainEvent $domainEvent
     * @param \Exception $exception
     */
    public function __construct(DomainEvent $domainEvent, \Exception $exception)
    {
        $this->domainEvent = $domainEvent;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "Failure during processing %s domain event with following message:\n\n%s",
            get_class($this->domainEvent),
            $this->exception->getMessage()
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return $this->exception;
    }
}
