<?php

namespace Crm\ApplicationModule\Tests;

use Nette\Caching\Cache;
use Nette\Caching\Storage;

/**
 * StaticMemoryStorage persists the cached data between the container rebuilds during tests, but doesn't persist
 * anything between different runs of tests.
 *
 * Its primary use is to store cached database schema so that DI doesn't need to generate the schema structure
 * after every DI container refresh.
 */
class StaticMemoryStorage implements Storage
{
    private static array $data = [];


    public function read(string $key): mixed
    {
        return self::$data[$key] ?? null;
    }


    public function lock(string $key): void
    {
    }


    public function write(string $key, $data, array $dependencies): void
    {
        self::$data[$key] = $data;
    }


    public function remove(string $key): void
    {
        unset(self::$data[$key]);
    }


    public function clean(array $conditions): void
    {
        if (!empty($conditions[Cache::All])) {
            self::$data = [];
        }
    }
}
