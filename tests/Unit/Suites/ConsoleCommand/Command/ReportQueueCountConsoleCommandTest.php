<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ReportQueueCountConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 */
class ReportQueueCountConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMasterFactory;

    /**
     * @var CLImate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCliMate;

    protected function setUp()
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), get_class_methods(CommonFactory::class)))
            ->getMock();

        $this->stubMasterFactory->method('getCommandMessageQueue')->willReturn($this->createMock(Queue::class));
        $this->stubMasterFactory->method('getEventMessageQueue')->willReturn($this->createMock(Queue::class));
        
        $this->mockCliMate = $this->getMockBuilder(CLImate::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CLImate::class), ['table']))
            ->getMock();
        $this->mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testReportsTheQueueCount()
    {
        $this->stubMasterFactory->getCommandMessageQueue()->method('count')->willReturn(111);
        $this->stubMasterFactory->getEventMessageQueue()->method('count')->willReturn(222);
        
        $this->mockCliMate->expects($this->once())->method('table')->with([
            [
                'Queue' => 'Command',
                'Count' => sprintf('%10d', 111),
            ],
            [
                'Queue' => 'Event',
                'Count' => sprintf('%10d', 222),
            ],
        ]);
        
        (new ReportQueueCountConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }
}
