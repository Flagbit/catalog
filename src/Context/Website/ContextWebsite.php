<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\Website\Exception\UnableToDetermineContextWebsiteException;
use LizardsAndPumpkins\Http\HttpRequest;

class ContextWebsite implements ContextPartBuilder
{
    /**
     * @var UrlToWebsiteMap
     */
    private $websiteMap;

    public function __construct(UrlToWebsiteMap $websiteMap)
    {
        $this->websiteMap = $websiteMap;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[Website::CONTEXT_CODE])) {
            return (string) $inputDataSet[Website::CONTEXT_CODE];
        }
        
        if (isset($inputDataSet[ContextBuilder::REQUEST])) {
            return (string) $this->getWebsiteFromRequest($inputDataSet[ContextBuilder::REQUEST]);
        }
        
        $message = 'Unable to determine context website because neither the ' .
            'website nor the request are set in the input array.';
        throw new UnableToDetermineContextWebsiteException($message);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return Website::CONTEXT_CODE;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getWebsiteFromRequest(HttpRequest $request)
    {
        return $this->websiteMap->getWebsiteCodeByUrl($request->getUrl());
    }
}
