<?php

namespace Brera\DataPool;

use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Http\HttpUrl;
use Brera\Product\ProductId;

abstract class AbstractDataPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stubKeyValueStore;

    /**
     * @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stubSearchEngine;

    protected function setUp()
    {
        $this->stubKeyValueStore = $this->getMock(KeyValueStore::class);
        $this->stubSearchEngine = $this->getMock(SearchEngine::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStubProductId()
    {
        $productId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $productId;
    }

    protected function addSetMethodToStubKeyValueStore()
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('set');
    }

    /**
     * @param string $returnValue
     */
    protected function addGetMethodToStubKeyValueStore($returnValue)
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('get')
            ->willReturn($returnValue);
    }

    /**
     * @param string[] $returnValue
     */
    protected function addMultiGetMethodToStubKeyValueStore($returnValue)
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('multiGet')
            ->willReturn($returnValue);
    }

    /**
     * @param boolean $returnResult
     */
    protected function addHasMethodToStubKeyValueStore($returnResult)
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('has')
            ->willReturn($returnResult);
    }

    /**
     * @return HttpUrl
     */
    protected function getDummyUrl()
    {
        $urlString = 'http://example.com/path';
        $url = HttpUrl::fromString($urlString);

        return $url;
    }
}
