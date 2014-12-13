<?php

namespace Brera\PoC;

class DataVersion
{
    /**
     * @var string
     */
    private $version;

    /**
     * @param string $version
     *
     * @return DataVersion
     * @throws EmptyVersionException
     */
    public static function fromVersionString($version)
    {
        if (!is_string($version) && !is_int($version) && !is_float($version)) {
            throw new InvalidVersionException();
        }

        if (empty($version)) {
            throw new EmptyVersionException();
        }

        return new self($version);
    }

    private function __construct($version)
    {
        $this->version = $version;
    }

    public function __toString()
    {
        return (string)$this->getVersion();
    }

    public function getVersion()
    {
        return $this->version;
    }
}
