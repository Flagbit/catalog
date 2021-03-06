<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\Context\Context;

interface SnippetKeyGenerator
{
    /**
     * @param Context $context
     * @param mixed[] $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data) : string;
}
