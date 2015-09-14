<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\MissingSnippetCodeMessage
 */
class MissingSnippetCodeMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MissingSnippetCodeMessage
     */
    private $message;

    /**
     * @var string[]
     */
    private $missingSnippetCodes;

    /**
     * @var string[]
     */
    private $stubContext;

    protected function setUp()
    {
        $this->missingSnippetCodes = ['foo', 'bar'];
        $this->stubContext = ['context' => $this->getMock(Context::class)];

        $this->message = new MissingSnippetCodeMessage($this->missingSnippetCodes, $this->stubContext);
    }

    public function testLogMessageIsReturned()
    {
        $expectation = 'Snippets contained in the page meta information where not loaded from the data pool (foo, bar)';

        $this->assertEquals($expectation, (string) $this->message);
    }

    public function testContextIsReturned()
    {
        $result = $this->message->getContext();

        $this->assertSame($this->stubContext, $result);
    }
}
