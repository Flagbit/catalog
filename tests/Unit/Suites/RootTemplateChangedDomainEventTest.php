<?php

namespace Brera;

/**
 * @covers \Brera\RootTemplateChangedDomainEvent
 */
class RootTemplateChangedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnSnippetLayoutHandle()
    {
        $layoutHandle = 'foo';
        $event = new RootTemplateChangedDomainEvent($layoutHandle);

        $result = $event->getXml();

        $this->assertEquals($layoutHandle, $result);
    }
}
