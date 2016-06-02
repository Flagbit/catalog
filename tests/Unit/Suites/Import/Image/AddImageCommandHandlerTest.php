<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\NoAddImageCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommand
 */
class AddImageCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var string
     */
    private $imageFilePath;

    /**
     * @var AddImageCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $fixtureDirectoryPath = $this->getUniqueTempDir();
        $this->imageFilePath = $fixtureDirectoryPath . '/foo.png';
        $this->createFixtureDirectory($fixtureDirectoryPath);
        $this->createFixtureFile($this->imageFilePath, '');

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
        $stubMessage = $this->getMock(Message::class, [], [], '', false);
        $stubMessage->method('getName')->willReturn('add_image');
        $testPayload = json_encode(['file_path' => $this->imageFilePath, 'data_version' => 'defg']);
        $stubMessage->method('getPayload')->willReturn($testPayload);

        $this->mockDomainEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);

        $this->commandHandler = new AddImageCommandHandler($stubMessage, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testImageWasAddedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('addVersioned')
            ->with('image_was_added', $this->isType('string'), $this->anything());

        $this->commandHandler->process();
    }
}
