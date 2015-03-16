<?php

namespace Brera;

interface LogMessage
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return mixed[]
     */
    public function getContext();
}
