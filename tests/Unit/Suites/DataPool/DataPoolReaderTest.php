<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyValueStoreKeyException;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 */
class DataPoolReaderTest extends AbstractDataPoolTest
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    protected function setUp()
    {
        parent::setUp();

        $this->dataPoolReader = new DataPoolReader(
            $this->getMockKeyValueStore(),
            $this->getMockSearchEngine(),
            $this->getMockUrlKeyStore()
        );
    }

    public function testSnippetIsReturnedIfExists()
    {
        $testValue = '<p>html</p>';
        $testKey = 'test';

        $this->addGetMethodToStubKeyValueStore($testValue);

        $this->assertEquals($testValue, $this->dataPoolReader->getSnippet($testKey));
    }

    /**
     * @dataProvider snippetsProvider
     * @param string $keyValueStorageReturn
     * @param string[] $expectedContent
     */
    public function testSnippetIsReturned(string $keyValueStorageReturn, array $expectedContent)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $this->assertEquals($expectedContent, $this->dataPoolReader->getChildSnippetKeys('some_key'));
    }

    /**
     * @return array[]
     */
    public function snippetsProvider() : array
    {
        return [
            [json_encode(false), []],
            ['[]', []],
            ['{}', []],
            [json_encode(['test_key1', 'test_key2', 'some_key']), ['test_key1', 'test_key2', 'some_key']],
        ];
    }

    public function testExceptionIsThrownIfJsonIsBroken()
    {
        $this->expectException(\RuntimeException::class);
        $this->addGetMethodToStubKeyValueStore('not a JSON string');
        $this->dataPoolReader->getChildSnippetKeys('some_key');
    }

    public function testOnlyStringKeyIsAcceptedForSnippets()
    {
        $this->expectException(\TypeError::class);
        $this->dataPoolReader->getChildSnippetKeys(1);
    }

    public function testOnlyStringKeysAreAcceptedForGetSnippet()
    {
        $this->expectException(\TypeError::class);
        $this->dataPoolReader->getSnippet(1);
    }

    public function testExceptionIsThrownIfTheKeyIsEmpty()
    {
        $this->expectException(InvalidKeyValueStoreKeyException::class);
        $this->dataPoolReader->getSnippet('');
    }

    public function testSnippetsAreReturned()
    {
        $keyValueStorageReturn = [
            'key' => 'value',
            'key2' => 'value2',
        ];
        $this->addMultiGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $snippets = $this->dataPoolReader->getSnippets(['key', 'key2']);

        $this->assertEquals($keyValueStorageReturn, $snippets);
    }

    public function testFalseIsReturnedIfASnippetKeyIsNotInTheStore()
    {
        $this->getMockKeyValueStore()->method('has')->with('test')->willReturn(false);
        $this->assertFalse($this->dataPoolReader->hasSnippet('test'));
    }

    public function testTrueIsReturnedIfASnippetKeyIsInTheStore()
    {
        $this->getMockKeyValueStore()->method('has')->with('test')->willReturn(true);
        $this->assertTrue($this->dataPoolReader->hasSnippet('test'));
    }

    public function testNegativeOneIsReturnedIfTheCurrentVersionIsNotSet()
    {
        $this->getMockKeyValueStore()->method('has')->with('current_version')->willReturn(false);
        $this->assertSame('-1', $this->dataPoolReader->getCurrentDataVersion());
    }

    public function testCurrentVersionIsReturned()
    {
        $currentDataVersion = '123';
        $this->getMockKeyValueStore()->method('has')->with('current_version')->willReturn(true);
        $this->getMockKeyValueStore()->method('get')->with('current_version')->willReturn($currentDataVersion);

        $this->assertSame($currentDataVersion, $this->dataPoolReader->getCurrentDataVersion());
    }

    public function testReturnsEmptyStringIfPreviousVersionIsNotPresent()
    {
        $this->getMockKeyValueStore()->method('has')->with('previous_version')->willReturn(false);
        $this->assertSame('', $this->dataPoolReader->getPreviousDataVersion());
    }

    public function testReturnsPreviousVersionIfPresent()
    {
        $this->getMockKeyValueStore()->method('has')->with('previous_version')->willReturn(true);
        $this->getMockKeyValueStore()->method('get')->with('previous_version')->willReturn('foo');
        $this->assertSame('foo', $this->dataPoolReader->getPreviousDataVersion());
    }

    public function testCriteriaQueriesAreDelegatedToSearchEngine()
    {
        /** @var QueryOptions|\PHPUnit_Framework_MockObject_MockObject $stubQueryOptions */
        $stubQueryOptions = $this->createMock(QueryOptions::class);

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->createMock(SearchCriteria::class);

        $this->getMockSearchEngine()->expects($this->once())->method('query')->with($stubCriteria, $stubQueryOptions);

        $this->dataPoolReader->getSearchResults($stubCriteria, $stubQueryOptions);
    }

    public function testItDelegatesUrlKeyReadsToUrlKeyStorage()
    {
        $expected = ['test.html'];
        $this->getMockUrlKeyStore()->expects($this->once())->method('getForDataVersion')->willReturn($expected);
        $this->assertSame($expected, $this->dataPoolReader->getUrlKeysForVersion('1.0'));
    }
}
