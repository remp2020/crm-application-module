<?php

namespace Crm\ApplicationModule\Models\Config;

use Crm\ApplicationModule\Models\Redis\RedisClientFactory;
use Crm\ApplicationModule\Models\Redis\RedisClientTrait;

class ConfigsCache
{
    use RedisClientTrait;

    const REDIS_KEY = 'configs';

    public function __construct(RedisClientFactory $redisClientFactory)
    {
        $this->redisClientFactory = $redisClientFactory;
    }

    public function add($key, $val)
    {
        return (bool)$this->redis()->hset(static::REDIS_KEY, $key, $val);
    }

    public function get($key, $default = null)
    {
        $val = $this->redis()->hget(static::REDIS_KEY, $key);
        if (!$val) {
            return $default;
        }
        return $val;
    }
}
