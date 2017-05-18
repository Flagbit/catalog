<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

class IntegrationTestUrlToWebsiteMap implements UrlToWebsiteMap
{
    public function getWebsiteCodeByUrl(string $url): Website
    {
        return Website::fromString('foo');
    }

    public function getRequestPathWithoutWebsitePrefix(string $url): string
    {
        return preg_match('#^https?://[^/]+/(?<path>.+)#', $url, $m) ? $m['path'] : '';
    }
}
