<?php

namespace Crm\ApplicationModule\Models\Config;

use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Tracy\Debugger;
use Tracy\ILogger;

class ApplicationConfig
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'integer';
    public const TYPE_TEXT = 'text';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_HTML = 'html';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_SELECT = 'select';

    public const CACHE_KEY = 'application_autoload_cache_v2';

    private bool $loaded = false;

    private ConfigsRepository $configsRepository;

    private LocalConfig $localConfig;

    private array $items = [];

    private Storage $cacheStorage;

    private int $cacheExpiration = 60;

    public function __construct(
        ConfigsRepository $configsRepository,
        LocalConfig $localConfig,
        Storage $cacheStorage
    ) {
        $this->configsRepository = $configsRepository;
        $this->localConfig = $localConfig;
        $this->cacheStorage = $cacheStorage;
    }

    public function setCacheExpiration(int $cacheExpiration): void
    {
        $this->cacheExpiration = $cacheExpiration;
    }

    /**
     * @return int|string|null
     */
    public function get(string $name)
    {
        $this->initAutoload();

        if (isset($this->items[$name])) {
            $item = $this->items[$name];
        } else {
            $itemRow = $this->configsRepository->loadByName($name);
            $item = $this->formatItem($itemRow);
            if ($this->cacheExpiration > 0) {
                // if any kind of caching is allowed, we can store this for future use
                $this->items[$name] = $item;
            }
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

    private function initAutoload(): bool
    {
        // items are loaded & cache expiration is non zero => nothing to autoload, items are loaded
        if ($this->loaded === true && $this->cacheExpiration > 0) {
            return false;
        }

        // items not loaded; expiration is non zero => try to autoload items from cache storage
        if ($this->cacheExpiration > 0) {
            $cacheData = $this->cacheStorage->read(self::CACHE_KEY);
            if ($cacheData) {
                $this->items = $cacheData;

                $this->loaded = true;
                return true;
            }
        }

        // cache is disabled (expiration is zero)
        // or cache data are missing (this is initial autoload, nothing was stored yet)
        $items = $this->configsRepository->loadAllAutoload();
        foreach ($items as $itemRow) {
            $this->items[$itemRow->name] = $this->formatItem($itemRow);
        }

        // write into cache storage only if cache expiration is non zero
        if ($this->cacheExpiration > 0) {
            $this->cacheStorage->write(self::CACHE_KEY, $this->items, [Cache::EXPIRE => $this->cacheExpiration]);
        }

        $this->loaded = true;
        return true;
    }

    private function formatItem($itemRow): ?object
    {
        if (!$itemRow) {
            return null;
        }
        return (object) [
            'value' => $itemRow->value,
            'type' => $itemRow->type,
        ];
    }

    /**
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
