<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

class ProductUrlKeyStoreTest extends AbstractIntegrationTest
{
    public function testUrlKeysAreWrittenToStore()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();
        
        $this->importCatalogFixture($factory, 'simple_product_armflasher-v1.xml');

        $this->failIfMessagesWhereLogged($factory->getLogger());
        
        $dataPoolReader = $factory->createDataPoolReader();

        $currentVersion = $dataPoolReader->getCurrentDataVersion();
        $urlKeys = $dataPoolReader->getUrlKeysForVersion($currentVersion);
        $this->assertNotEmpty($urlKeys);
    }
}
