<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\UpdateMultipleProductStockQuantityCommandHandler
 * @uses   \Brera\Product\UpdateMultipleProductStockQuantityCommand
 * @uses   \Brera\Product\UpdateProductStockQuantityCommand
 */
class UpdateMultipleProductStockQuantityCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateMultipleProductStockQuantityCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommand;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var UpdateMultipleProductStockQuantityCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductStockQuantitySource1 = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $stubProductStockQuantitySource1->method('getProductId')->willReturn($stubProductId);
        $stubProductStockQuantitySource2 = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $stubProductStockQuantitySource2->method('getProductId')->willReturn($stubProductId);
        $stubProductStockQuantitySourceArray = [$stubProductStockQuantitySource1, $stubProductStockQuantitySource2];

        $this->mockCommand = $this->getMock(UpdateMultipleProductStockQuantityCommand::class, [], [], '', false);
        $this->mockCommand->method('getProductStockQuantitySourceArray')
            ->willReturn($stubProductStockQuantitySourceArray);

        $this->mockCommandQueue = $this->getMock(Queue::class);

        $this->commandHandler = new UpdateMultipleProductStockQuantityCommandHandler(
            $this->mockCommand,
            $this->mockCommandQueue
        );
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testDomainEventCommandIsPutIntoCommandQueue()
    {
        $this->mockCommandQueue->expects($this->exactly(2))
            ->method('add')
            ->with($this->isInstanceOf(UpdateProductStockQuantityCommand::class));
        $this->commandHandler->process();
    }
}
