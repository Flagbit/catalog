<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope;

/**
 * @covers \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @covers \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerDirectiveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EnqueuesMessageEnvelope|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

    public static $shutdownWasCalled = false;

    private function createHandler($message): ShutdownWorkerDirectiveHandler
    {
        return new ShutdownWorkerDirectiveHandler($message, $this->mockQueue);
    }

    protected function setUp()
    {
        self::$shutdownWasCalled = false;
        $this->mockQueue = $this->createMock(EnqueuesMessageEnvelope::class);
    }

    public function testImplementsCommandAndEventHandlerInterfaces()
    {
        $directive = new ShutdownWorkerDirective('*');
        $handler = $this->createHandler($directive->toMessage());
        $this->assertInstanceOf(CommandHandler::class, $handler);
        $this->assertInstanceOf(DomainEventHandler::class, $handler);
    }

    public function testRetriesCommandIfMessagePidValueDoesNotMatchWithIncrementedRetryCount()
    {
        $sourceDirective = new ShutdownWorkerDirective(strval(getmypid() - 1), 42);
        $this->mockQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (ShutdownWorkerDirective $retryDirctive) use ($sourceDirective) {
                $this->assertSame($sourceDirective->getRetryCount() + 1, $retryDirctive->getRetryCount());
            });
        $this->createHandler($sourceDirective->toMessage())->process();
    }

    public function testRetriesCommandUpToMaxRetryBoundary()
    {
        $this->mockQueue->expects($this->once())->method('add');
        $retry = new ShutdownWorkerDirective(strval(getmypid() - 1), ShutdownWorkerDirectiveHandler::MAX_RETRIES - 1);
        $this->createHandler($retry->toMessage())->process();
    }

    public function testDoesNotRetryCommandIfTheMaxRetryBoundaryIsReached()
    {
        $this->mockQueue->expects($this->never())->method('add');
        $retry = new ShutdownWorkerDirective(strval(getmypid() - 1), ShutdownWorkerDirectiveHandler::MAX_RETRIES);
        $this->createHandler($retry->toMessage())->process();
    }

    public function testDoesNotCallExitIfMessagePidDoesNotMatchCurrentPid()
    {
        $command = new ShutdownWorkerDirective(strval(getmypid() - 1));
        $this->createHandler($command->toMessage())->process();
        $this->assertFalse(self::$shutdownWasCalled, "The shutdown() function was unexpectedly called");
    }

    public function testCallsExitIfNumericMessagePidMatchesCurrentPid()
    {
        $command = new ShutdownWorkerDirective((string) getmypid());
        $this->createHandler($command->toMessage())->process();
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }

    public function testCallsExitForWildcardPidInMessage()
    {
        $command = new ShutdownWorkerDirective('*');
        $this->createHandler($command->toMessage())->process();
        $this->assertTrue(self::$shutdownWasCalled, "The shutdown() function was not called");
    }
}

function shutdown()
{
    ShutdownWorkerDirectiveHandlerTest::$shutdownWasCalled = true;
}
