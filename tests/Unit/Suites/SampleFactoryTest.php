<?php

namespace Brera\Tests\Integration;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\FileSearchEngine;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessingStrategySequence;
use Brera\LocalFilesystemStorageReader;
use Brera\LocalFilesystemStorageWriter;
use Brera\Queue\File\FileQueue;
use Brera\SampleMasterFactory;
use Brera\SampleFactory;
use Brera\InMemoryLogger;

/**
 * @covers \Brera\SampleFactory
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\DataPool\KeyValue\File\FileKeyValueStore
 * @uses   \Brera\DataPool\SearchEngine\FileSearchEngine
 * @uses   \Brera\Image\ImageMagickInscribeStrategy
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessingStrategySequence
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\MasterFactoryTrait
 */
class SampleFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleFactory
     */
    private $factory;

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $this->factory = new SampleFactory();
        $masterFactory->register($this->factory);
    }

    protected function tearDown()
    {
        $keyValueStoragePath = sys_get_temp_dir() . '/brera/key-value-store';
        if (file_exists($keyValueStoragePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($keyValueStoragePath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $path) {
                $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }
            rmdir($keyValueStoragePath);
        }
    }

    public function testFileKeyValueStoreIsReturned()
    {
        $this->assertInstanceOf(FileKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    public function testFileSearchEngineIsReturned()
    {
        $this->assertInstanceOf(FileSearchEngine::class, $this->factory->createSearchEngine());
    }

    public function testInMemoryEventQueueIsReturned()
    {
        $this->assertInstanceOf(FileQueue::class, $this->factory->createEventQueue());
    }

    public function testInMemoryCommandQueueIsReturned()
    {
        $this->assertInstanceOf(FileQueue::class, $this->factory->createCommandQueue());
    }

    public function testInMemoryLoggerIsReturned()
    {
        $this->assertInstanceOf(InMemoryLogger::class, $this->factory->createLogger());
    }

    public function testArrayOfSearchableAttributeCodesIsReturned()
    {
        $result = $this->factory->getSearchableAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    public function testArrayOfProductListingFilterNavigationAttributeCodesIsReturned()
    {
        $result = $this->factory->getProductListingFilterNavigationAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    public function testArrayOfProductSearchResultsFilterNavigationAttributeCodesIsReturned()
    {
        $result = $this->factory->getProductSearchResultsFilterNavigationAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    public function testImageProcessorCollectionIsReturned()
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    public function testEnlargedImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getOriginalImageProcessor());
    }

    public function testOriginalImageFileStorageReaderIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getOriginalImageFileStorageReader()
        );
    }

    public function testOriginalImageFileStorageWriterIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getOriginalImageFileStorageWriter()
        );
    }

    public function testEnlargedImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->getOriginalImageProcessingStrategySequence()
        );
    }

    public function testProductDetailsPageImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getProductDetailsPageImageProcessor());
    }

    public function testProductDetailsPageImageFileStorageReaderIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getProductDetailsPageImageFileStorageReader()
        );
    }

    public function testProductDetailsPageImageFileStorageWriterIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getProductDetailsPageImageFileStorageWriter()
        );
    }

    public function testProductDetailsPageImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->getProductDetailsPageImageProcessingStrategySequence()
        );
    }

    public function testProductListingImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getProductListingImageProcessor());
    }

    public function testProductListingImageFileStorageReaderIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getProductListingImageFileStorageReader()
        );
    }

    public function testProductListingImageFileStorageWriterIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getProductListingImageFileStorageWriter()
        );
    }

    public function testProductListingImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->getProductListingImageProcessingStrategySequence()
        );
    }

    public function testGalleyThumbnailImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getGalleyThumbnailImageProcessor());
    }

    public function testGalleyThumbnailImageFileStorageReaderIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getGalleyThumbnailImageFileStorageReader()
        );
    }

    public function testGalleyThumbnailImageFileStorageWriterIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getGalleyThumbnailImageFileStorageWriter()
        );
    }

    public function testGalleyThumbnailImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->getGalleyThumbnailImageProcessingStrategySequence()
        );
    }
}
