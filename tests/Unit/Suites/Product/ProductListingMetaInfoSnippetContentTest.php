<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
 */
class ProductListingMetaInfoSnippetContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectionCriteria;

    protected function setUp()
    {
        $this->selectionCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $this->selectionCriteria->expects($this->any())
            ->method('jsonSerialize')
            ->willReturn(['condition' => SearchCriteria::AND_CONDITION, 'criteria' => []]);

        $this->pageMetaInfo = ProductListingMetaInfoSnippetContent::create(
            $this->selectionCriteria,
            $this->rootSnippetCode,
            [$this->rootSnippetCode]
        );
    }

    public function testArrayIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getInfo());
    }

    public function testExpectedArrayKeysArePresentInJsonContent()
    {
        $keys = [
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA,
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES
        ];
        foreach ($keys as $key) {
            $this->assertTrue(
                array_key_exists($key, $this->pageMetaInfo->getInfo()),
                sprintf('The expected key "%s" is not set on the page meta info array', $key)
            );
        }
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsNoString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        ProductListingMetaInfoSnippetContent::create($this->selectionCriteria, 1.0, []);
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::create($this->selectionCriteria, $rootSnippetCode, []);
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    public function testJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(ProductListingMetaInfoSnippetContent::class, $pageMetaInfo);
    }
    
    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->setExpectedException(\OutOfBoundsException::class);
        ProductListingMetaInfoSnippetContent::fromJson('malformed-json');
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $key
     */
    public function testExceptionIsThrownIfARequiredKeyIsMissing($key)
    {
        $this->setExpectedException(\RuntimeException::class, 'Missing key in input JSON');
        $pageInfo = $this->pageMetaInfo->getInfo();
        unset($pageInfo[$key]);
        ProductListingMetaInfoSnippetContent::fromJson(json_encode($pageInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductListingMetaInfoSnippetContent::KEY_CRITERIA],
            [ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES],
        ];
    }

    public function testSelectionCriteriaIsReturned()
    {
        $this->assertEquals($this->selectionCriteria, $this->pageMetaInfo->getSelectionCriteria());
    }

    public function testRootSnippetCodeIsReturned()
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getPageSnippetCodes());
    }

    /**
     * @test
     * @expectedException \Brera\Product\MalformedSearchCriteriaMetaException
     * @expectedExceptionMessage Missing criteria condition.
     */
    public function itShouldFailIfSearchCriteriaConditionIsMissing()
    {
        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MalformedSearchCriteriaMetaException
     * @expectedExceptionMessage Malformed criteria.
     */
    public function itShouldFailIfSearchCriteriaCriteriaIsMissing()
    {
        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => ['condition' => ''],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MalformedSearchCriteriaMetaException
     * @expectedExceptionMessage Malformed criterion.
     */
    public function itShouldFailIfSearchCriteriaCriterionIsInvalid()
    {
        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => ['']
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MalformedSearchCriteriaMetaException
     * @expectedExceptionMessage Missing criterion field name.
     */
    public function itShouldFailIfCriterionFieldNameIsMissing()
    {
        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [[]]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MalformedSearchCriteriaMetaException
     * @expectedExceptionMessage Missing criterion field value.
     */
    public function itShouldFailIfCriterionFieldValueIsMissing()
    {
        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [
                    ['fieldName' => 'foo']
                ]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MalformedSearchCriteriaMetaException
     * @expectedExceptionMessage Missing criterion operation.
     */
    public function itShouldFailIfCriterionOperationIsMissing()
    {
        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar']
                ]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    /**
     * @test
     */
    public function itShouldCreateAProductListingMetaInfoWithPassedSearchCriteria()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'eq';

        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [
                    ['fieldName' => $fieldName, 'fieldValue' => $fieldValue, 'operation' => $operation]
                ]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::fromJson($json);
        $criteria = $metaSnippetContent->getSelectionCriteria()->getCriteria();

        $this->assertEquals($fieldName, $criteria[0]->getFieldName());
        $this->assertEquals($fieldValue, $criteria[0]->getFieldValue());
        $this->assertEquals($operation, $criteria[0]->getOperation());
    }
}
