<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\WebsiteContextDecorator
 * @covers \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\InternalContextState
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\DataVersion
 */
class WebsiteContextDecoratorTest extends ContextDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return 'website';
    }

    /**
     * @return array
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-website-code'];
    }
    
    /**
     * @param Context $stubContext
     * @param array $stubContextData
     * @return WebsiteContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new WebsiteContextDecorator($stubContext, $stubContextData);
    }
}
