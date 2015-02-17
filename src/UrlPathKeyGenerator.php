<?php


namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;

interface UrlPathKeyGenerator
{
    /**
     * @param string $path
     * @param Context $context
     * @return string
     */
    public function getUrlKeyForPathInContext($path, Context $context);

    /**
     * @param HttpUrl $url
     * @param Context $context
     * @return string
     */
    public function getUrlKeyForUrlInContext(HttpUrl $url, Context $context);

    /**
     * @param string $rootSnippetKey
     * @return string
     * @todo this is not the right class, move to a better place
     */
    public function getChildSnippetListKey($rootSnippetKey);
}
