<?php

namespace Crm\ApplicationModule\Config;

use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class ApplicationConfig
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'integer';
    const TYPE_TEXT = 'text';
    const TYPE_PASSWORD = 'password';
    const TYPE_HTML = 'html';
    const TYPE_BOOLEAN = 'boolean';

    private $loaded = false;

    private $configsRepository;

    private $localConfig;

    private $items = null;

    private $cacheStorage;

    private $cacheExpiration = 60;

    public function __construct(
        ConfigsRepository $configsRepository,
        LocalConfig $localConfig,
        IStorage $cacheStorage
    ) {
        $this->configsRepository = $configsRepository;
        $this->localConfig = $localConfig;
        $this->cacheStorage = $cacheStorage;
    }

    public function setCacheExpiration($cacheExpiration)
    {
        $this->cacheExpiration = $cacheExpiration;
    }

    public function get($name)
    {
        if (!$this->loaded) {
            $this->initAutoload();
        }

        if (isset($this->items[$name])) {
            $item = $this->items[$name];
            $value = $item->value;

            if ($this->localConfig->exists($name)) {
                $value = $this->localConfig->value($name);
            }

            return $this->formatValue($value, $item->type);
        }

        $item = $this->configsRepository->loadByName($name);
        if ($item) {
            $value = $item->value;

            if ($this->localConfig->exists($name)) {
                $value = $this->localConfig->value($name);
            }

            return $this->formatValue($value, $item->type);
        }

        // tu mozno bude treba hodit excepnut
        return null;
    }

    private function initAutoload()
    {
        $cacheData = $this->cacheStorage->read('application_autoload_cache');
        if ($cacheData) {
            $this->items = $cacheData;
        } else {
            $items = $this->configsRepository->loadAllAutoload();
            foreach ($items as $item) {
                $this->items[$item->name] = (object)$item->toArray();
            }
            $this->cacheStorage->write('application_autoload_cache', $this->items, [Cache::EXPIRE => $this->cacheExpiration]);
        }
        $this->loaded = true;
    }

    /**
     * @param $value
     * @param string $type
     * @return int|string
     */
    private function formatValue($value, $type = 'string')
    {
        if ($type == self::TYPE_INT) {
            return intval($value);
        }

        return $value;
    }
}
