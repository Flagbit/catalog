<?php


namespace Brera\Projection\Catalog\Import;

use Brera\Product\ProductSource;
use Brera\TestFileFixtureTrait;
use SebastianBergmann\Money\XXX;

/**
 * @covers Brera\Projection\Catalog\Import\CatalogXmlParser
 */
class CatalogXmlParserTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @return string
     */
    private function getListingXml()
    {
        return <<<EOT
        <listing url_key="lizards" condition="and" website="test1" locale="xx_XX">
            <category operation="=">category-1</category>
            <brand operation="=">Lizards</brand>
        </listing>
EOT;
    }

    /**
     * @return string
     */
    private function getSimpleProductXml()
    {
        return <<<EOT
        <product type="simple" sku="test-sku" visible="true" tax_class_id="123">
            <attributes>
                <image>
                    <main>true</main>
                    <file>first-image.jpg</file>
                    <label>The main image label</label>
                </image>
                <image>
                    <show_in_gallery>false</show_in_gallery>
                    <file>second-image.png</file>
                    <label locale="xx_XX">Second image label XX</label>
                    <label locale="yy_YY">Second image label YY</label>
                </image>
                <category website="test1" locale="xx_XX">category-1</category>
                <category website="test2" locale="xx_XX">category-1</category>
                <category website="test2" locale="yy_YY">category-1</category>
                <stock_qty>111</stock_qty>
                <backorders>true</backorders>
                <url_key locale="xx_XX">xx-url-key</url_key>
                <url_key locale="yy_YY">yy-url-key</url_key>
                <name>Test Product Definition</name>
                <price website="test1">9.99</price>
                <price website="test2">7.99</price>
                <special_price website="test2">5.99</special_price>
                <description><![CDATA[A Description with some <strong>Tags</strong>]]></description>
                <brand>Lizards</brand>
                <style>Pumpkin</style>
            </attributes>
        </product>
EOT;
    }

    /**
     * @param string $content
     * @return string
     */
    private function getProductSectionWithContent($content)
    {
        return sprintf('
    <products>
    %s
    </products>
', $content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function getListingSectionWithContent($content)
    {
        return sprintf('
    <listings>
    %s
    </listings>
', $content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function getCatalogXmlWithContent($content)
    {
        return sprintf('<catalog  xmlns="http://brera.io" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    %s
</catalog>', $content);
    }

    /**
     * @return string
     */
    private function getCatalogXmlWithOneSimpleProduct()
    {
        return $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent(
                $this->getSimpleProductXml()
            )
        );
    }

    /**
     * @return string
     */
    public function getCatalogXmlWithTwoSimpleProducts()
    {
        return $this->getCatalogXmlWithContent(
            $this->getProductSectionWithContent(
                $this->getSimpleProductXml() .
                $this->getSimpleProductXml()
            )
        );
    }

    /**
     * @return string
     */
    private function getCatalogXmlWithTwoListings()
    {
        return $this->getCatalogXmlWithContent(
            $this->getListingSectionWithContent(
                $this->getListingXml() .
                $this->getListingXml()
            )
        );
    }

    /**
     * @param string $filePath ∂
     * @param string $content
     */
    private function createFixtureFileAndPathWithContent($filePath, $content)
    {
        $this->createFixtureDirectory(dirname($filePath));
        $this->createFixtureFile($filePath, $content);
    }

    /**
     * @return string
     */
    private function createCatalogXmlFileWithOneSimpleProduct()
    {
        $filePath = $this->getUniqueTempDir() . '/simple-product.xml';
        $this->createFixtureFileAndPathWithContent($filePath, $this->getCatalogXmlWithOneSimpleProduct());
        return $filePath;
    }

    /**
     * @param mixed $expectedXml
     * @param int $expectedCallCount
     * @return \Closure|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCallback($expectedXml, $expectedCallCount)
    {
        $expected = new \DOMDocument();
        $expected->loadXML($expectedXml);
        $mockCallback = $this->getMock(Callback::class, ['__invoke']);
        $mockCallback->expects($this->exactly($expectedCallCount))->method('__invoke')->willReturnCallback(
            function ($xml) use ($expected) {
                $actual = new \DOMDocument();
                $actual->loadXML($xml);
                $this->assertEqualXMLStructure($expected->firstChild, $actual->firstChild);
            }
        );
        return $mockCallback;
    }

    /**
     * @param int|object|null $invalidSourceFilePath
     * @param string $expectedType
     * @dataProvider invalidSourceFilePathDataProvider
     */
    public function testItThrowsAnExceptionIfTheFromFileConstructorInputIsNotAString(
        $invalidSourceFilePath,
        $expectedType
    ) {
        $this->setExpectedException(
            Exception\CatalogImportSourceFilePathIsNotAStringException::class,
            sprintf('Expected the catalog XML import file path to be a string, got "%s"', $expectedType)
        );
        CatalogXmlParser::fromFilePath($invalidSourceFilePath);
    }

    /**
     * @param int|object|null $noXmlStringInput
     * @param string $expectedType
     * @dataProvider invalidSourceFilePathDataProvider
     */
    public function testItThrowsAnExceptionIfTheFromXmlConstructorInputIsNotAString(
        $noXmlStringInput,
        $expectedType
    ) {
        $this->setExpectedException(
            Exception\CatalogImportSourceXMLNotAStringException::class,
            sprintf('Expected the catalog XML to be a string, got "%s"', $expectedType)
        );
        CatalogXmlParser::fromXml($noXmlStringInput);
    }

    /**
     * @return array[]
     */
    public function invalidSourceFilePathDataProvider()
    {
        return [
            [null, 'NULL'],
            [42, 'integer'],
            [new \stdClass(), 'stdClass'],
        ];
    }

    public function testItThrowsAnExceptionIfTheInputFileDoesNotExist()
    {
        $sourceFilePath = 'non-existent-file.xml';
        $this->setExpectedException(
            Exception\CatalogImportSourceXmlFileDoesNotExistException::class,
            sprintf('The catalog XML import file "%s" does not exist', $sourceFilePath)
        );
        CatalogXmlParser::fromFilePath($sourceFilePath);
    }

    public function testItThrowsAnExceptionIfTheInputFileIsNotReadable()
    {
        $dirPath = $this->getUniqueTempDir();
        $sourceFilePath = $dirPath . '/not-readable.xml';
        $this->createFixtureDirectory($dirPath);
        $this->createFixtureFile($sourceFilePath, '', 0000);

        $this->setExpectedException(
            Exception\CatalogImportSourceXmlFileIsNotReadableException::class,
            sprintf('The catalog XML import file "%s" is not readable', $sourceFilePath)
        );
        CatalogXmlParser::fromFilePath($sourceFilePath);
    }

    public function testItReturnsACatalogXmlParserInstanceFromAFile()
    {
        $instance = CatalogXmlParser::fromFilePath($this->createCatalogXmlFileWithOneSimpleProduct());
        $this->assertInstanceOf(CatalogXmlParser::class, $instance);
    }

    public function testItReturnsACatalogXmlParserInstanceFromAXmlString()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithOneSimpleProduct());
        $this->assertInstanceOf(CatalogXmlParser::class, $instance);
    }

    public function testItCallsAllRegisteredProductSourceCallbacksForOneProduct()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithOneSimpleProduct());
        $expectedXml = $this->getSimpleProductXml();
        $expectedCallCount = 1;
        $instance->registerProductSourceCallback($this->createMockCallback($expectedXml, $expectedCallCount));
        $instance->registerProductSourceCallback($this->createMockCallback($expectedXml, $expectedCallCount));
        $instance->parse();
    }

    public function testItCallsRegisteredProductSourceCallbackForTwoProducts()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithTwoSimpleProducts());
        $expectedXml = $this->getSimpleProductXml();
        $expectedCallCount = 2;
        $instance->registerProductSourceCallback($this->createMockCallback($expectedXml, $expectedCallCount));
        $instance->parse();
    }

    public function testItCallsAllRegisteredListingCallbacks()
    {
        $instance = CatalogXmlParser::fromXml($this->getCatalogXmlWithTwoListings());
        $expectedXml = $this->getListingXml();
        $expectedCallCount = 2;
        $instance->registerListingCallback($this->createMockCallback($expectedXml, $expectedCallCount));
        $instance->parse();
    }
}
