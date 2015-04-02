<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\CustomerGroupContextDecorator
 * @covers \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\InternalContextState
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\DataVersion
 */
class CustomerGroupContextDecoratorTest extends ContextDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return 'customer_group';
    }

    /**
     * @return array
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-customer-group-code'];
    }

    /**
     * @param Context $stubContext
     * @param array $stubContextData
     * @return CustomerGroupContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new CustomerGroupContextDecorator($stubContext, $stubContextData);
    }
}
