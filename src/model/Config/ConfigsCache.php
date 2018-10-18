<?php

namespace Crm\ApplicationModule\Config;

use Predis\Client;

class ConfigsCache
{
    const REDIS_KEY = 'configs';

    /** @var Client */
    private $redis;

    private $host;

    private $port;

    private $db;

    public function __construct($host = '127.0.0.1', $port = 6379, $db = 0)
    {
        $this->host = $host ?? '127.0.0.1';
        $this->port = $port ?? 6379;
        $this->db = $db;
    }

    private function connect()
    {
        if (!$this->redis) {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host'   => $this->host,
                'port'   => $this->port,
            ]);
            if ($this->db) {
                $this->redis->select($this->db);
            }
        }

        return $this->redis;
    }

    public function add($key, $val)
    {
        return (bool)$this->connect()->hset(static::REDIS_KEY, $key, $val);
    }

    public function get($key, $default = null)
    {
        $val = $this->connect()->hget(static::REDIS_KEY, $key);
        if (!$val) {
            return $default;
        }
        return $val;
    }
}
