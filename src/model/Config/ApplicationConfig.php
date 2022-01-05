<?php

namespace Crm\ApplicationModule\Config;

use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Tracy\Debugger;
use Tracy\ILogger;

class ApplicationConfig
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'integer';
    const TYPE_TEXT = 'text';
    const TYPE_PASSWORD = 'password';
    const TYPE_HTML = 'html';
    const TYPE_BOOLEAN = 'boolean';

    private bool $loaded = false;

    private ConfigsRepository $configsRepository;

    private LocalConfig $localConfig;

    private array $items;

    private IStorage $cacheStorage;

    private int $cacheExpiration = 60;

    public function __construct(
        ConfigsRepository $configsRepository,
        LocalConfig $localConfig,
        IStorage $cacheStorage
    ) {
        $this->configsRepository = $configsRepository;
        $this->localConfig = $localConfig;
        $this->cacheStorage = $cacheStorage;
    }

    public function setCacheExpiration(int $cacheExpiration)
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
        } else {
            $itemRow = $this->configsRepository->loadByName($name);
            $item = $this->formatItem($itemRow);
            $this->items[$name] = $item;
        }

        if ($item) {
            $value = $item->value;

            if ($this->localConfig->exists($name)) {
                $value = $this->localConfig->value($name);
            }

            return $this->formatValue($value, $item->type);
        }

        Debugger::log("Requested config '{$name}' doesn't exist, returning 'null'.", ILogger::WARNING);
        return null;
    }

    private function initAutoload()
    {
        $cacheData = $this->cacheStorage->read('application_autoload_cache_v2');
        if ($cacheData) {
            $this->items = $cacheData;
        } else {
            $items = $this->configsRepository->loadAllAutoload();
            foreach ($items as $itemRow) {
                $this->items[$itemRow->name] = $this->formatItem($itemRow);
            }
            $this->cacheStorage->write('application_autoload_cache_v2', $this->items, [Cache::EXPIRE => $this->cacheExpiration]);
        }
        $this->loaded = true;
    }

    private function formatItem($itemRow)
    {
        return (object) [
            'value' => $itemRow->value,
            'type' => $itemRow->type,
        ];
    }

    /**
     * @param $value
     * @param string $type
     * @return int|string
     */
    private function formatValue($value, string $type = 'string')
    {
        if ($type === self::TYPE_INT) {
            return (int) $value;
        }

        return $value;
    }
}
