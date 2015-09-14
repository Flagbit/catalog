<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\FailedToReadFromDomainEventQueueMessage
 */
class FailedToReadFromDomainEventQueueMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FailedToReadFromDomainEventQueueMessage
     */
    private $message;

    /**
     * @var \Exception
     */
    private $stubException;

    protected function setUp()
    {
        $this->stubException = new \Exception('foo');
        $this->message = new FailedToReadFromDomainEventQueueMessage($this->stubException);
    }

    public function testLogMessageIsReturned()
    {
        $result = (string) $this->message;
        $expectation = "Failed to read from domain event queue message with following exception:\n\nfoo";

        $this->assertEquals($expectation, $result);

    }

    public function testExceptionContextIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->stubException], $result);
    }
}
