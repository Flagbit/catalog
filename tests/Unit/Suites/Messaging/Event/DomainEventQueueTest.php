<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class DomainEventQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataVersion;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $addToQueueSpy;

    private function getMessagesAddedToQueue(): array
    {
        return array_map(function (\PHPUnit_Framework_MockObject_Invocation_Static $invocation) {
            return $invocation->parameters[0];
        }, $this->addToQueueSpy->getInvocations());
    }

    private function getAddedMessage(): Message
    {
        $messages = $this->getMessagesAddedToQueue();
        if (count($messages) === 0) {
            $this->fail('No messages added to queue');
        }
        return $messages[0];
    }

    private function assertAddedMessageCount(int $expected)
    {
        $queueMessages = $this->getMessagesAddedToQueue();
        $message = sprintf('Expected queue message count to be %d, got %d', $expected, count($queueMessages));
        $this->assertCount($expected, $queueMessages, $message);
    }

    protected function setUp()
    {
        $this->mockQueue = $this->getMock(Queue::class);
        $this->addToQueueSpy = $this->any();
        $this->mockQueue->expects($this->addToQueueSpy)->method('add');

        $this->eventQueue = new DomainEventQueue($this->mockQueue);
        $this->mockDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
    }

    public function testAddsDomainEventToMessageQueue()
    {
        $this->eventQueue->addVersioned('foo', 'bar', $this->mockDataVersion);
        $this->assertAddedMessageCount(1);
    }

    public function testAddsDomainEventNameSuffixIfNotPresent()
    {
        $name = 'foo';
        $payload = 'bar';

        $this->eventQueue->addVersioned($name, $payload, $this->mockDataVersion);
        $message = $this->getAddedMessage();
        $this->assertSame($name . '_domain_event', $message->getName());
    }

    public function testDoesNotAtDomainEventNameSuffixIfPresent()
    {
        $name = 'foo_domain_event';
        $payload = 'bar';

        $this->eventQueue->addVersioned($name, $payload, $this->mockDataVersion);
        $message = $this->getAddedMessage();
        $this->assertSame($name, $message->getName());
    }

    public function testCreatesVersionedQueueMessage()
    {
        $name = 'foo';
        $payload = 'bar';

        $this->eventQueue->addVersioned($name, $payload, $this->mockDataVersion);

        $message = $this->getAddedMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame(['data_version' => (string)($this->mockDataVersion)], $message->getMetadata());
    }

    public function testCreatesUnVersionedQueueMessage()
    {
        $name = 'foo';
        $payload = 'bar';

        $this->eventQueue->addNotVersioned($name, $payload);

        $message = $this->getAddedMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame([], $message->getMetadata());
    }
}
