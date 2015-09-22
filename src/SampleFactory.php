<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValue\File\FileKeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FileSearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore;
use LizardsAndPumpkins\Image\ImageMagickInscribeStrategy;
use LizardsAndPumpkins\Image\ImageProcessor;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageProcessingStrategySequence;
use LizardsAndPumpkins\Log\InMemoryLogger;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\Writer\FileLogMessageWriter;
use LizardsAndPumpkins\Log\Writer\LogMessageWriter;
use LizardsAndPumpkins\Log\WritingLoggerDecorator;
use LizardsAndPumpkins\Queue\File\FileQueue;
use LizardsAndPumpkins\Queue\Queue;

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
        return ['gender', 'brand', 'price', 'color'];
    }

    /**
     * @return string[]
     */
    public function getProductSearchResultsFilterNavigationAttributeCodes()
    {
        return ['gender', 'brand', 'category', 'price', 'color'];
    }

    /**
     * @return FileKeyValueStore
     */
    public function createKeyValueStore()
    {
        $baseStorageDir = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $baseStorageDir . '/lizards-and-pumpkins/key-value-store';
        $this->createDirectoryIfNotExists($storagePath);

        return new FileKeyValueStore($storagePath);
    }

    /**
     * @return Queue
     */
    public function createEventQueue()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $storageBasePath . '/lizards-and-pumpkins/event-queue/content';
        $lockFile = $storageBasePath . '/lizards-and-pumpkins/event-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return Queue
     */
    public function createCommandQueue()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $storageBasePath . '/lizards-and-pumpkins/command-queue/content';
        $lockFile = $storageBasePath . '/lizards-and-pumpkins/command-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return Logger
     */
    public function createLogger()
    {
        return new WritingLoggerDecorator(
            new InMemoryLogger(),
            $this->getMasterFactory()->createLogMessageWriter()
        );
    }

    /**
     * @return LogMessageWriter
     */
    public function createLogMessageWriter()
    {
        return new FileLogMessageWriter($this->getMasterFactory()->getLogFilePathConfig());
    }

    /**
     * @return string
     */
    public function getLogFilePathConfig()
    {
        return __DIR__ . '/../log/system.log';
    }

    /**
     * @return FileSearchEngine
     */
    public function createSearchEngine()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $searchEngineStoragePath = $storageBasePath . '/lizards-and-pumpkins/search-engine';
        $this->createDirectoryIfNotExists($searchEngineStoragePath);

        return FileSearchEngine::create($searchEngineStoragePath);
    }

    /**
     * @return FileUrlKeyStore
     */
    public function createUrlKeyStore()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        return new FileUrlKeyStore($storageBasePath . '/lizards-and-pumpkins/url-key-store');
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->createOriginalImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createProductDetailsPageImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createProductListingImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createGalleyThumbnailImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createSearchAutosuggestionImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function createOriginalImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createOriginalImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createOriginalImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createOriginalImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createOriginalImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createOriginalImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/original';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createOriginalImageProcessingStrategySequence()
    {
        return new ImageProcessingStrategySequence();
    }

    /**
     * @return ImageProcessor
     */
    public function createProductDetailsPageImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createProductDetailsPageImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createProductDetailsPageImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createProductDetailsPageImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createProductDetailsPageImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createProductDetailsPageImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/large';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createProductDetailsPageImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(365, 340, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function createProductListingImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createProductListingImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createProductListingImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createProductListingImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createProductListingImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createProductListingImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/medium';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createProductListingImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(188, 115, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function createGalleyThumbnailImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createGalleyThumbnailImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createGalleyThumbnailImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createGalleyThumbnailImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createGalleyThumbnailImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createGalleyThumbnailImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/small';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createGalleyThumbnailImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(48, 48, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function createSearchAutosuggestionImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createSearchAutosuggestionImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createSearchAutosuggestionImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createSearchAutosuggestionImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createSearchAutosuggestionImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createSearchAutosuggestionImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/search-autosuggestion';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createSearchAutosuggestionImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(60, 37, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return string
     */
    public function getFileStorageBasePathConfig()
    {
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();
        $basePath = $configReader->get('file_storage_base_path');
        return null === $basePath ?
            sys_get_temp_dir() :
            $basePath;
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
