<?php


namespace LizardsAndPumpkins\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\ContentDelivery\PageBuilder\Exception\InvalidSnippetContentException;
use LizardsAndPumpkins\ContentDelivery\PageBuilder\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\ContentDelivery\PageBuilder\Exception\PageContentBuildAlreadyTriggeredException;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\PageBuilder\PageBuilderSnippets
 */
class PageBuilderSnippetsTest extends \PHPUnit_Framework_TestCase
{
    private $testKey = 'a-key';

    private $testCode = 'a-code';

    private $testContent = 'some content';

    /**
     * @var PageBuilderSnippets
     */
    private $pageSnippets;

    protected function setUp()
    {
        $codeToKeyMap = [$this->testCode => $this->testKey];
        $keyToContentMap = [$this->testKey => $this->testContent];
        $this->pageSnippets = PageBuilderSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
    }

    public function testItReturnsAPageSnippetInstance()
    {
        $codeToKeyMap = [];
        $keyToContentMap = [];
        $pageSnippets = PageBuilderSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
        $this->assertInstanceOf(PageBuilderSnippets::class, $pageSnippets);
    }

    public function testItImplementsThePageSnippetsInterface()
    {
        $this->assertInstanceOf(PageSnippets::class, $this->pageSnippets);
    }

    public function testItReturnsTheNotLoadedSnippetCodes()
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $pageSnippets = PageBuilderSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
        $this->assertSame(['missing'], $pageSnippets->getNotLoadedSnippetCodes());
    }

    public function testItReturnsTheLoadedSnippetCodes()
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $pageSnippets = PageBuilderSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
        $this->assertSame(['found'], $pageSnippets->getSnippetCodes());
    }

    public function testItReturnsTheSnippetContentForAGivenKey()
    {
        $this->assertSame('some content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItReturnsTheSnippetContentForAGivenCode()
    {
        $this->assertSame('some content', $this->pageSnippets->getSnippetByCode($this->testCode));
    }

    public function testItUpdatesASnippetWithTheGivenKey()
    {
        $this->pageSnippets->updateSnippetByKey($this->testKey, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItThrowsAnExceptionIfTheGivenKeyIsNotKnown()
    {
        $this->setExpectedException(
            NonExistingSnippetException::class,
            'The snippet key "not-existing-key" does not exist on the current page'
        );
        $this->pageSnippets->updateSnippetByKey('not-existing-key', 'new content');
    }

    public function testItThrowsAnExceptionIfTheSnippetContentIsNotAStringWithKeySpec()
    {
        $this->setExpectedException(
            InvalidSnippetContentException::class,
            'Invalid snippet content for the key "a-key" specified: expected string, got "NULL"'
        );
        $this->pageSnippets->updateSnippetByKey('a-key', null);
    }

    public function testItUpdatesASnippetWithTheGivenCode()
    {
        $this->pageSnippets->updateSnippetByCode($this->testCode, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItThrowsAnExceptionWhenUpdatingANonExistingSnippet()
    {
        $this->setExpectedException(
            NonExistingSnippetException::class,
            'The snippet code "not-existing-code" does not exist on the current page'
        );
        $this->pageSnippets->updateSnippetByCode('not-existing-code', 'new content');
    }

    public function testItThrowsAnExceptionIfTheSnippetContentIsNotAStringWithCodeSpec()
    {
        $this->setExpectedException(
            InvalidSnippetContentException::class,
            'Invalid snippet content for the code "a-code" specified: expected string, got "integer"'
        );
        $this->pageSnippets->updateSnippetByCode($this->testCode, 123);
    }

    public function testItThrowsAnExceptionIfThePageIsBuiltTwice()
    {
        $this->setExpectedException(
            PageContentBuildAlreadyTriggeredException::class,
            'The method buildPageContent() may only be called once an an instance'
        );
        $this->pageSnippets->buildPageContent($this->testCode);
        $this->pageSnippets->buildPageContent($this->testCode);
    }

    public function testItReturnsTrueIfASnippetIsPresent()
    {
        $this->assertTrue($this->pageSnippets->hasSnippetCode($this->testCode));
    }

    public function testItReturnsFalseIfASnippetIsNotPresent()
    {
        $this->assertFalse($this->pageSnippets->hasSnippetCode('not-present-code'));
    }
}
