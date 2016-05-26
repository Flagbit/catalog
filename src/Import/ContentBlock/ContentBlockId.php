<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\ContentBlock;

class ContentBlockId
{
    /**
     * @var string
     */
    private $contentBlockIdString;

    /**
     * @param string $contentBlockIdString
     */
    private function __construct($contentBlockIdString)
    {
        $this->contentBlockIdString = $contentBlockIdString;
    }

    public static function fromString(string $contentBlockIdString): ContentBlockId
    {
        // todo: guard against empty content block id's
        return new self($contentBlockIdString);
    }

    public function __toString(): string
    {
        return $this->contentBlockIdString;
    }
}
