<?php

namespace Brera\Poc\Tests\Unit;

use Brera\PoC\KeyValue\InMemoryKeyValueStore,
    Brera\PoC\KeyValue\KeyNotFoundException;

/**
 * @package Brera\Poc
 * @covers  Brera\PoC\InMemoryKeyValueStore
 */
class InMemoryKeyValueStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryKeyValueStore
     */
    private $store;

    public function setUp()
    {
        $this->store = new InMemoryKeyValueStore();
    }

    /**
     * @test
     */
    public function itShouldSetAndGetAValue()
    {
        $key = 'key';
        $value = 'value';

        $this->store->set($key, $value);
        $this->assertEquals($value, $this->store->get($key));
    }

    /**
     * @test
     */
    public function itShouldNotHasBeforeSettingAValue()
    {
        $key = 'key';
        $value = 'value';

        $this->assertFalse($this->store->has($key));

        $this->store->set($key, $value);
        $this->assertTrue($this->store->has($key));
    }

    /**
     * @test
     * @expectedException KeyNotFoundException
     */
    public function itShouldThrowAnExcptionWhenValueIsNotSet()
    {
        $this->store->get('not set key');
    }
}
