<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\Website\Website;

class IntegrationTestContextSource extends ContextSource
{
    /**
     * @return mixed[]
     */
    protected function getContextMatrix()
    {
        return [
            [Website::CONTEXT_CODE => 'ru', Locale::CONTEXT_CODE => 'de_DE'],
            [Website::CONTEXT_CODE => 'ru', Locale::CONTEXT_CODE => 'en_US'],
            [Website::CONTEXT_CODE => 'cy', Locale::CONTEXT_CODE => 'de_DE'],
            [Website::CONTEXT_CODE => 'cy', Locale::CONTEXT_CODE => 'en_US'],
            [Website::CONTEXT_CODE => 'fr', Locale::CONTEXT_CODE => 'fr_FR'],
        ];
    }
}
