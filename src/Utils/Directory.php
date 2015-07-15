<?php

namespace Brera\Utils;

class Directory
{
    /**
     * @var string
     */
    private $directoryPath;

    /**
     * @param string $directoryPath
     */
    private function __construct($directoryPath)
    {
        $this->directoryPath = $directoryPath;
    }

    /**
     * @param string $directoryPath
     * @return Directory
     * @throws InvalidDirectoryPathException
     * @throws FileAlreadyExistsWithinGivenPathException
     */
    public static function fromPath($directoryPath)
    {
        if (!is_string($directoryPath)) {
            throw new InvalidDirectoryPathException(
                sprintf('Directory path is supposed to be a string, %s given.', gettype($directoryPath))
            );
        }

        if (is_file($directoryPath)) {
            throw new FileAlreadyExistsWithinGivenPathException(
                sprintf('File already exists withing the path %s.', $directoryPath)
            );
        }

        return new self($directoryPath);
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return is_readable($this->directoryPath);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->directoryPath;
    }
}
