<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\DomainEventHandler;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\LogMessage;

/**
 * @covers \LizardsAndPumpkins\Projection\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Projection\DomainEventProcessedLogMessage
 */
class ProcessTimeLoggingDomainEventHandlerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDecoratedEventHandler;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var ProcessTimeLoggingDomainEventHandlerDecorator;
     */
    private $decorator;

    protected function setUp()
    {
        $this->mockDecoratedEventHandler = $this->getMock(DomainEventHandler::class);
        $this->mockLogger = $this->getMock(Logger::class);
        $this->decorator = new ProcessTimeLoggingDomainEventHandlerDecorator(
            $this->mockDecoratedEventHandler,
            $this->mockLogger
        );
    }

    public function testItImplementsDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->decorator);
    }

    public function testItDelegatesProcessingToComponent()
    {
        $this->mockDecoratedEventHandler->expects($this->once())->method('process');
        $this->decorator->process();
    }

    public function testItLogsEachCallToProcess()
    {
        $this->mockLogger->expects($this->once())->method('log');
        $this->decorator->process();
    }

    public function testTheMessageFormat()
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->willReturnCallback(function (LogMessage $logMessage) {
                if (!preg_match('/^DomainEventHandler::process [a-z0-9_\\\]+ \d+\.\d+/i', (string)$logMessage)) {
                    $message = sprintf('%s format does not expectation, got "%s"', get_class($logMessage), $logMessage);
                    $this->fail($message);
                }
            });
        $this->decorator->process();
    }
}
