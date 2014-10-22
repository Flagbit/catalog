<?php

namespace Brera\PoC;

/**
 * @covers \Brera\PoC\InMemoryLogger
 */
class InMemoryLoggerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldStoreMessageInMemory()
	{
		$message = 'test-message';

		$logger = new InMemoryLogger();
		$logger->log(null, $message);

		$messages = $logger->getMessages();

		$this->assertContains($message, $messages);
	}
}
