<?php
namespace Brera\Context;

use Brera\DataVersion;

class VersionedContext implements Context
{
    const CODE = 'version';
    
    /**
     * @var DataVersion
     */
    private $version;

    /**
     * @param DataVersion $version
     */
    public function __construct(DataVersion $version)
    {
        $this->version = $version;
    }

    /**
     * @param string $code
     * @return string
     * @throws ContextCodeNotFoundException
     */
    public function getValue($code)
    {
        if (self::CODE !== $code) {
            throw new ContextCodeNotFoundException(sprintf(
                "No value was not found in the current context for the code '%s'",
                $code
            ));
        }
        return (string) $this->version;
    }

    /**
     * @return string[]
     */
    public function getSupportedCodes()
    {
        return [self::CODE];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'v:' . $this->version;
    }

    /**
     * @param string[] $requestedParts
     * @return string
     */
    public function getIdForParts(array $requestedParts)
    {
        return in_array(self::CODE, $requestedParts) ?
            $this->getId() :
            '';
    }

    /**
     * @param string $code
     * @return bool
     */
    public function supportsCode($code)
    {
        return $code == self::CODE;
    }
}
