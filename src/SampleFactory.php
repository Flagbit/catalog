<?php

namespace Brera;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\FileSearchEngine;
use Brera\Image\ImageMagickInscribeStrategy;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessingStrategySequence;
use Brera\Queue\File\FileQueue;
use Brera\Queue\InMemory\InMemoryQueue;

class SampleFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return ['name', 'category', 'brand'];
    }

    /**
     * @return string[]
     */
    public function getProductListingFilterNavigationAttributeCodes()
    {
        return ['brand', 'gender'];
    }

    /**
     * @return string[]
     */
    public function getProductSearchResultsFilterNavigationAttributeCodes()
    {
        return ['brand', 'category', 'gender'];
    }

    /**
     * @return FileKeyValueStore
     */
    public function createKeyValueStore()
    {
        $storagePath = sys_get_temp_dir() . '/brera/key-value-store';
        $this->createDirectoryIfNotExists($storagePath);

        return new FileKeyValueStore($storagePath);
    }

    /**
     * @return InMemoryQueue
     */
    public function createEventQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/event-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/event-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return InMemoryQueue
     */
    public function createCommandQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/command-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/command-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return InMemoryLogger
     */
    public function createLogger()
    {
        return new InMemoryLogger();
    }

    /**
     * @return FileSearchEngine
     */
    public function createSearchEngine()
    {
        $searchEngineStoragePath = sys_get_temp_dir() . '/brera/search-engine';
        $this->createDirectoryIfNotExists($searchEngineStoragePath);

        return FileSearchEngine::create($searchEngineStoragePath);
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->getOriginalImageProcessor());
        $processorCollection->add($this->getMasterFactory()->getProductDetailsPageImageProcessor());
        $processorCollection->add($this->getMasterFactory()->getProductListingImageProcessor());
        $processorCollection->add($this->getMasterFactory()->getGalleyThumbnailImageProcessor());
        $processorCollection->add($this->getMasterFactory()->getSearchAutosuggestionImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function getOriginalImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->getOriginalImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->getOriginalImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getOriginalImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getOriginalImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function getOriginalImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/original';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function getOriginalImageProcessingStrategySequence()
    {
        return new ImageProcessingStrategySequence();
    }

    /**
     * @return ImageProcessor
     */
    public function getProductDetailsPageImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->getProductDetailsPageImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->getProductDetailsPageImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getProductDetailsPageImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getProductDetailsPageImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function getProductDetailsPageImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/large';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function getProductDetailsPageImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(365, 340, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function getProductListingImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->getProductListingImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->getProductListingImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getProductListingImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getProductListingImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function getProductListingImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/medium';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function getProductListingImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(188, 115, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function getGalleyThumbnailImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->getGalleyThumbnailImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->getGalleyThumbnailImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getGalleyThumbnailImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getGalleyThumbnailImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function getGalleyThumbnailImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/small';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function getGalleyThumbnailImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(48, 48, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function getSearchAutosuggestionImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->getSearchAutosuggestionImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->getSearchAutosuggestionImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getSearchAutosuggestionImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getSearchAutosuggestionImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function getSearchAutosuggestionImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/search-autosuggestion';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function getSearchAutosuggestionImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(60, 37, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @param string $path
     */
    private function createDirectoryIfNotExists($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}
