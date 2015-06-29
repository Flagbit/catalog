<?php

namespace Brera;

/**
 * @covers \Brera\InMemoryLogger
 */
class InMemoryLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testMessageIsStoredInMemory()
    {
        $stubLogMessage = $this->getMock(LogMessage::class);

        $logger = new InMemoryLogger();
        $logger->log($stubLogMessage);

        $messages = $logger->getMessages();

        $this->assertContains($stubLogMessage, $messages);
    }
}
