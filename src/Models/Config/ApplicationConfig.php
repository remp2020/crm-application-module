<?php

namespace Crm\ApplicationModule\Models\Config;

use Crm\ApplicationModule\Repositories\ConfigsRepository;
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

    public const CACHE_KEY = 'application_config_cache';

    /** @var array<string, object{type: string, value: string}> */
    private array $items = [];

    private int $cacheExpiration = 60;
    private int $lastConfigRefreshTimestamp = 0;

    public function __construct(
        private readonly ConfigsRepository $configsRepository,
        private readonly LocalConfig $localConfig,
        private readonly Storage $cacheStorage,
    ) {
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
        if ($this->needsRefresh()) {
            $this->refresh();
        }

        $item = $this->items[$name] ?? null;
        if ($item === null) {
            Debugger::log("Requested config '{$name}' doesn't exist, returning 'null'.", ILogger::WARNING);
            return null;
        }

        $value = $this->localConfig->exists($name)
            ? $this->localConfig->value($name)
            : $item->value;

        return $this->formatValue($value, $item->type);
    }

    public function refresh(bool $force = false): void
    {
        $isCacheEnabled = $this->cacheExpiration > 0;

        if ($isCacheEnabled && !$force) {
            $cacheData = $this->cacheStorage->read(self::CACHE_KEY);

            if ($cacheData) {
                $this->items = $cacheData;
                $this->lastConfigRefreshTimestamp = time();
                return;
            }
        }

        $items = $this->configsRepository->all();
        foreach ($items as $itemRow) {
            $this->items[$itemRow->name] = $this->formatItem($itemRow);
        }

        if ($isCacheEnabled || $force) {
            $this->cacheStorage->write(self::CACHE_KEY, $this->items, [Cache::EXPIRE => $this->cacheExpiration]);
        }

        $this->lastConfigRefreshTimestamp = time();
    }

    private function needsRefresh(): bool
    {
        $isCacheEnabled = $this->cacheExpiration > 0;
        if (!$isCacheEnabled) {
            return true;
        }

        $refreshAt = $this->lastConfigRefreshTimestamp + $this->cacheExpiration;
        return time() > $refreshAt;
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
