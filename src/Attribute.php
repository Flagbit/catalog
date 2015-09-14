<?php

namespace LizardsAndPumpkins;

interface Attribute
{
    /**
     * @param string $codeExpectation
     * @return bool
     */
    public function isCodeEqualsTo($codeExpectation);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return mixed
     */
    public function getValue();
}
