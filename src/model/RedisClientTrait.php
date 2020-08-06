<?php

namespace Crm\ApplicationModule;

use Predis\Client;

trait RedisClientTrait
{
    /** @var RedisClientFactory */
    protected $redisClientFactory;

    /** @var Client */
    protected $redis;

    protected $database;

    protected $prefixRedisKeys = false;

    public function setDatabase($database)
    {
        $this->database = $database;
    }

    public function usePrefix(bool $usePrefix = true): void
    {
        $this->prefixRedisKeys = $usePrefix;
    }

    protected function redis()
    {
        if (!$this->redisClientFactory || !($this->redisClientFactory instanceof RedisClientFactory)) {
            throw new RedisClientTraitException('In order to use `RedisClientTrait`, you need to initialize `RedisClientFactory $redisClientFactory` in your service');
        }

        if ($this->redis === null) {
            $this->redis = $this->redisClientFactory->getClient($this->database, $this->prefixRedisKeys);
        }

        return $this->redis;
    }
}
