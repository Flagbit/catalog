<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\UrlKey;

/**
 * @covers \LizardsAndPumpkins\Projection\UrlKeyForContext
 * @uses   \LizardsAndPumpkins\UrlKey
 */
class UrlKeyForContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKey
     */
    private $testUrlKey;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var UrlKeyForContext
     */
    private $urlKeyForContext;

    protected function setUp()
    {
        $this->testUrlKey = UrlKey::fromString('example.html');
        $this->stubContext = $this->getMock(Context::class);
        $this->urlKeyForContext = new UrlKeyForContext($this->testUrlKey, $this->stubContext);
    }

    public function testItReturnsTheUrlKey()
    {
        $this->assertSame($this->testUrlKey, $this->urlKeyForContext->getUrlKey());
    }

    public function testItReturnsTheContext()
    {
        $this->assertSame($this->stubContext, $this->urlKeyForContext->getContext());
    }

    public function testItReturnsTheUrlKeyString()
    {
        $this->assertSame((string)$this->testUrlKey, (string)$this->urlKeyForContext);
    }
}
