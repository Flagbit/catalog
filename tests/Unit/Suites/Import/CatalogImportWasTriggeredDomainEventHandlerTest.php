<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class CatalogImportWasTriggeredDomainEventHandlerTest extends \PHPUnit\Framework\TestCase
{
    private $testFile = '/test.xml';

    /**
     * @var CatalogImport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCatalogImport;

    /**
     * @var CatalogImportWasTriggeredDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $this->mockCatalogImport = $this->createMock(CatalogImport::class);
        $this->domainEventHandler = new CatalogImportWasTriggeredDomainEventHandler(
            $this->mockCatalogImport,
            (new CatalogImportWasTriggeredDomainEvent(DataVersion::fromVersionString('foo'), $this->testFile))->toMessage()
        );
    }
    
    public function testImplementsDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testDelegatesProcessingTheImportFileToCatalogImport()
    {
        $this->mockCatalogImport->expects($this->once())->method('importFile')->with($this->testFile);
        $this->domainEventHandler->process();
    }
}
