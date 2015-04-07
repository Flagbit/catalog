<?php


namespace Brera\Tests\Integration;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\FileSearchEngine;
use Brera\SampleFactory;
use Brera\InMemoryLogger;
use Brera\Queue\InMemory\InMemoryQueue;

/**
 * @covers \Brera\SampleFactory
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\DataPool\KeyValue\File\FileKeyValueStore
 * @uses   \Brera\DataPool\SearchEngine\FileSearchEngine
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 */
class SampleFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new SampleFactory();
    }

    /**
     * @test
     */
    public function itShouldCreateAFileKeyValueStore()
    {
        $this->assertInstanceOf(FileKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    /**
     * @test
     */
    public function itShouldCreateAnInMemorySearchEngine()
    {
        $this->assertInstanceOf(FileSearchEngine::class, $this->factory->createSearchEngine());
    }

    /**
     * @test
     */
    public function itShouldCreateAnInMemoryEventQueue()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createEventQueue());
    }

    /**
     * @test
     */
    public function itShouldCreateAnInMemoryLogger()
    {
        $this->assertInstanceOf(InMemoryLogger::class, $this->factory->createLogger());
    }

    /**
     * @test
     */
    public function itShouldCreateSearchableAttributeCodesArray()
    {
        $this->assertInternalType('array', $this->factory->getSearchableAttributeCodes());
    }
}
