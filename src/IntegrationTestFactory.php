<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\FacetFieldRangeCollection;
use LizardsAndPumpkins\ContentDelivery\Catalog\FacetFilterConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\FacetFilterConfigCollection;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationCollection;
use LizardsAndPumpkins\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Image\ImageMagickResizeStrategy;
use LizardsAndPumpkins\Image\ImageProcessor;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageProcessingStrategySequence;
use LizardsAndPumpkins\Log\InMemoryLogger;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Queue\InMemory\InMemoryQueue;
use LizardsAndPumpkins\Queue\Queue;

class IntegrationTestFactory implements Factory
{
    use FactoryTrait;

    const PROCESSED_IMAGES_DIR = 'lizards-and-pumpkins/processed-images';
    const PROCESSED_IMAGE_WIDTH = 40;
    const PROCESSED_IMAGE_HEIGHT = 20;

    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    /**
     * @var SortOrderConfig[]
     */
    private $lazyLoadedProductListingSortOrderConfig;

    /**
     * @var SortOrderConfig[]
     */
    private $lazyLoadedProductSearchSortOrderConfig;

    /**
     * @var SortOrderConfig
     */
    private $lazyLoadedProductSearchAutosuggestionSortOrderConfig;

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
    public function getProductListingFilterNavigationConfig()
    {
        return new FacetFilterConfigCollection(
            new FacetFilterConfig(
                AttributeCode::fromString('gender'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            ),
            new FacetFilterConfig(
                AttributeCode::fromString('brand'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            ),
            new FacetFilterConfig(
                AttributeCode::fromString('price'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            ),
            new FacetFilterConfig(
                AttributeCode::fromString('color'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            )
        );
    }

    /**
     * @return string[]
     */
    public function getProductSearchResultsFilterNavigationConfig()
    {
        return new FacetFilterConfigCollection(
            new FacetFilterConfig(
                AttributeCode::fromString('gender'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            ),
            new FacetFilterConfig(
                AttributeCode::fromString('brand'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            ),
            new FacetFilterConfig(
                AttributeCode::fromString('category'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            ),
            new FacetFilterConfig(
                AttributeCode::fromString('price'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            ),
            new FacetFilterConfig(
                AttributeCode::fromString('color'),
                new FacetFieldRangeCollection,
                new FacetFieldTransformationCollection
            )
        );
    }

    /**
     * @return InMemoryKeyValueStore
     */
    public function createKeyValueStore()
    {
        return new InMemoryKeyValueStore();
    }

    /**
     * @return InMemoryQueue
     */
    public function createEventQueue()
    {
        return new InMemoryQueue();
    }

    /**
     * @return InMemoryQueue
     */
    public function createCommandQueue()
    {
        return new InMemoryQueue();
    }

    /**
     * @return InMemoryLogger
     */
    public function createLogger()
    {
        return new InMemoryLogger();
    }

    /**
     * @return InMemorySearchEngine
     */
    public function createSearchEngine()
    {
        return new InMemorySearchEngine(
            $this->getMasterFactory()->createSearchCriteriaBuilder()
        );
    }

    /**
     * @return UrlKeyStore
     */
    public function createUrlKeyStore()
    {
        return new InMemoryUrlKeyStore();
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->createImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function createImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();
        
        $resultImageDir = $this->getMasterFactory()->getFileStorageBasePathConfig() . '/' . self::PROCESSED_IMAGES_DIR;
        
        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter, $resultImageDir);
    }

    /**
     * @return FileStorageReader
     */
    public function createFileStorageReader()
    {
        return new LocalFilesystemStorageReader();
    }

    /**
     * @return FileStorageWriter
     */
    public function createFileStorageWriter()
    {
        return new LocalFilesystemStorageWriter();
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createImageProcessingStrategySequence()
    {
        $imageResizeStrategyClass = $this->locateImageResizeStrategyClass();
        $imageResizeStrategy = new $imageResizeStrategyClass(
            self::PROCESSED_IMAGE_WIDTH,
            self::PROCESSED_IMAGE_HEIGHT
        );

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return string
     */
    private function locateImageResizeStrategyClass()
    {
        if (extension_loaded('imagick')) {
            return ImageMagickResizeStrategy::class;
        }
        return Image\GdResizeStrategy::class;
    }

    /**
     * @return KeyValueStore
     */
    public function getKeyValueStore()
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->createKeyValueStore();
        }
        return $this->keyValueStore;
    }

    public function setKeyValueStore(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
    }

    /**
     * @return Queue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }
        return $this->eventQueue;
    }

    public function setEventQueue(Queue $eventQueue)
    {
        $this->eventQueue = $eventQueue;
    }

    /**
     * @return Queue
     */
    public function getCommandQueue()
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->createCommandQueue();
        }
        return $this->commandQueue;
    }

    public function setCommandQueue(Queue $commandQueue)
    {
        $this->commandQueue = $commandQueue;
    }

    /**
     * @return SearchEngine
     */
    public function getSearchEngine()
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->createSearchEngine();
        }
        return $this->searchEngine;
    }

    public function setSearchEngine(SearchEngine $searchEngine)
    {
        $this->searchEngine = $searchEngine;
    }

    /**
     * @return UrlKeyStore
     */
    public function getUrlKeyStore()
    {
        if (null === $this->urlKeyStore) {
            $this->urlKeyStore = $this->createUrlKeyStore();
        }
        return $this->urlKeyStore;
    }

    public function setUrlKeyStore(UrlKeyStore $urlKeyStore)
    {
        $this->urlKeyStore = $urlKeyStore;
    }

    /**
     * @return string
     */
    public function getFileStorageBasePathConfig()
    {
        return sys_get_temp_dir();
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductListingSortOrderConfig()
    {
        if (null === $this->lazyLoadedProductListingSortOrderConfig) {
            $this->lazyLoadedProductListingSortOrderConfig = [
                SortOrderConfig::createSelected(AttributeCode::fromString('name'), SearchEngine::SORT_DIRECTION_ASC),
            ];
        }

        return $this->lazyLoadedProductListingSortOrderConfig;
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductSearchSortOrderConfig()
    {
        if (null === $this->lazyLoadedProductSearchSortOrderConfig) {
            $this->lazyLoadedProductSearchSortOrderConfig = [
                SortOrderConfig::createSelected(AttributeCode::fromString('name'), SearchEngine::SORT_DIRECTION_ASC),
            ];
        }

        return $this->lazyLoadedProductSearchSortOrderConfig;
    }

    /**
     * @return SortOrderConfig
     */
    public function getProductSearchAutosuggestionSortOrderConfig()
    {
        if (null === $this->lazyLoadedProductSearchAutosuggestionSortOrderConfig) {
            $this->lazyLoadedProductSearchAutosuggestionSortOrderConfig = SortOrderConfig::createSelected(
                AttributeCode::fromString('name'),
                SearchEngine::SORT_DIRECTION_ASC
            );
        }

        return $this->lazyLoadedProductSearchAutosuggestionSortOrderConfig;
    }
}
