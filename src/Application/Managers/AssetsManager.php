<?php

namespace Crm\ApplicationModule\Application\Managers;

use Nette\Utils\Strings;

class AssetsManager
{
    private $assetsDir;

    private $copyIntents = [];

    public function __construct(string $assetsDir)
    {
        $this->assetsDir = $assetsDir;
    }

    /**
     * Register intent to copy vendor directory to assets directory
     * Actual copying is initiated by command InstallAssets
     *
     * @param string $source      absolute directory path, has to be under APP_ROOT directory
     * @param string $destination relative directory under $this->assetsDir path (typically '%appDir%/www')
     *
     * @throws \Exception
     */
    public function copyAssets(string $source, string $destination)
    {
        if (!str_starts_with(realpath($source), APP_ROOT)) {
            if (realpath($source) === false) {
                throw new \Exception('registered assets source path does not exist: ' . $source);
            }

            throw new \Exception('assets source path is not under APP_ROOT path (' . APP_ROOT . '): ' . $source);
        }

        $destinationPath = $this->assetsDir . '/' . $destination;

        $this->copyIntents[] = [$source, $destinationPath];
    }

    public function getAssetsDir(): string
    {
        return $this->assetsDir;
    }

    public function setAssetsDir(string $assetsDir)
    {
        $this->assetsDir = $assetsDir;
    }

    /**
     * @return array list of [sourceDirectory, destinationDirectory] values
     */
    public function getCopyIntents(): array
    {
        return $this->copyIntents;
    }

    public function checkAssetsFileExist(string $assetsFile): bool
    {
        return file_exists($this->assetsDir . '/' . $assetsFile);
    }
}
