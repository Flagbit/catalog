<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandHandlerFailedMessage
 */
class CommandHandlerFailedMessageTest extends TestCase
{
    /**
     * @var \Exception
     */
    private $testException;

    /**
     * @var CommandHandlerFailedMessage
     */
    private $message;

    /**
     * @var string
     */
    private $exceptionMessage = 'foo';

    protected function setUp()
    {
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn('test_command');

        $this->testException = new \Exception($this->exceptionMessage);

        $this->message = new CommandHandlerFailedMessage($stubCommand, $this->testException);
    }

    public function testLogMessageIsReturned()
    {
        $expectation = sprintf(
            "Failure during processing test_command command with following message:\n\n%s",
            $this->exceptionMessage
        );

        $this->assertEquals($expectation, (string) $this->message);
    }

    public function testExceptionContextIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->testException], $result);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis()
    {
        $synopsis = $this->message->getContextSynopsis();
        $this->assertContains($this->testException->getFile(), $synopsis);
        $this->assertContains((string) $this->testException->getLine(), $synopsis);
    }
}
