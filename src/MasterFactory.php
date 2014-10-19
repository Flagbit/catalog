<?php

namespace Brera\PoC;

interface MasterFactory
{
    /**
     * @param Factory $factory
     * @return mixed
     */
    public function register(Factory $factory);
}
