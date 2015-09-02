<?php


namespace Brera\Context\Stubs;

use Brera\Context\ContextDecorator;

class TestContextDecorator extends ContextDecorator
{
    /**
     * @return string
     */
    protected function getCode()
    {
        return 'request_test';
    }

    /**
     * @return mixed[]
     */
    public function getRawSourceDataForTest()
    {
        return $this->getSourceData();
    }
}