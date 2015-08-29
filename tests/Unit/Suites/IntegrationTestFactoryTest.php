<?php

namespace Brera\Tests\Integration;

use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessingStrategySequence;
use Brera\IntegrationTestFactory;
use Brera\InMemoryLogger;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\LocalFilesystemStorageReader;
use Brera\LocalFilesystemStorageWriter;
use Brera\Logger;
use Brera\Queue\Queue;
use Brera\SampleMasterFactory;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\IntegrationTestFactory
 * @uses   \Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\Image\ImageMagickResizeStrategy
 * @uses   \Brera\Image\GdResizeStrategy
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessingStrategySequence
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 * @uses   \Brera\Utils\LocalFilesystem
 */
class IntegrationTestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    public function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $this->factory = new IntegrationTestFactory();
        $masterFactory->register($this->factory);
    }

    public function testInMemoryKeyValueStoreIsReturned()
    {
        $this->assertInstanceOf(InMemoryKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    public function testInMemoryEventQueueIsReturned()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createEventQueue());
    }

    public function testInMemoryCommandQueueIsReturned()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createCommandQueue());
    }

    public function testInMemoryLoggerIsReturned()
    {
        $this->assertInstanceOf(InMemoryLogger::class, $this->factory->createLogger());
    }

    public function testInMemorySearchEngineIsReturned()
    {
        $this->assertInstanceOf(InMemorySearchEngine::class, $this->factory->createSearchEngine());
    }

    public function testLocalFilesystemStorageWriterIsReturned()
    {
        $this->assertInstanceOf(LocalFilesystemStorageWriter::class, $this->factory->createImageFileStorageWriter());
    }

    public function testLocalFilesystemStorageReaderIsReturned()
    {
        $this->assertInstanceOf(LocalFilesystemStorageReader::class, $this->factory->createImageFileStorageReader());
    }

    public function testResizedImagesDirectoryIsCreated()
    {
        $resultImageDir = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;

        (new LocalFilesystem())->removeDirectoryAndItsContent($resultImageDir);

        $this->factory->createImageFileStorageWriter();

        $this->assertTrue(is_dir($resultImageDir));
    }

    public function testImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->createImageProcessingStrategySequence()
        );
    }

    public function testArrayOfSearchableAttributeCodesIsReturned()
    {
        $this->assertInternalType('array', $this->factory->getSearchableAttributeCodes());
    }

    public function testImageProcessorCollectionIsReturned()
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    public function testImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->createImageProcessor());
    }

    public function testItReturnsTheSameKeyValueStoreInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(KeyValueStore::class, $this->factory->getKeyValueStore());
        $this->assertSame($this->factory->getKeyValueStore(), $this->factory->getKeyValueStore());
    }

    public function testItReturnsTheSetKeyValueStore()
    {
        $stubKeyValueStore = $this->getMock(KeyValueStore::class);
        $this->factory->setKeyValueStore($stubKeyValueStore);
        $this->assertSame($stubKeyValueStore, $this->factory->getKeyValueStore());
    }

    public function testItReturnsTheSameEventQueueInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(Queue::class, $this->factory->getEventQueue());
        $this->assertSame($this->factory->getEventQueue(), $this->factory->getEventQueue());
    }

    public function testItReturnsTheSetEventQueue()
    {
        $stubEventQueue = $this->getMock(Queue::class);
        $this->factory->setEventQueue($stubEventQueue);
        $this->assertSame($stubEventQueue, $this->factory->getEventQueue());
    }

    public function testItReturnsTheSameCommandQueueInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(Queue::class, $this->factory->getCommandQueue());
        $this->assertSame($this->factory->getCommandQueue(), $this->factory->getCommandQueue());
    }

    public function testItReturnsTheSetCommandQueue()
    {
        $stubCommandQueue = $this->getMock(Queue::class);
        $this->factory->setCommandQueue($stubCommandQueue);
        $this->assertSame($stubCommandQueue, $this->factory->getCommandQueue());
    }

    public function testItReturnsTheSameSearchEngineOnMultipleCalls()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->factory->getSearchEngine());
        $this->assertSame($this->factory->getSearchEngine(), $this->factory->getSearchEngine());
    }

    public function testItReturnsTheSetSearchEngine()
    {
        $stubSearchEngine = $this->getMock(SearchEngine::class);
        $this->factory->setSearchEngine($stubSearchEngine);
        $this->assertSame($stubSearchEngine, $this->factory->getSearchEngine());
    }
}
