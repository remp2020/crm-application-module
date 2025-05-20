<?php

namespace Crm\ApplicationModule\Models\Redis;

use Predis\Client;

class RedisClientFactory
{
    private $host;
    private $port;
    private $password;
    private $database;
    private $keysPrefix;

    // replica-related parameters
    private $enableReplication = false;
    private $service;
    private $sentinels;

    public function __construct($host, $port, $password, $database, ?string $keysPrefix = null)
    {
        $this->host = $host ?? '127.0.0.1';
        $this->port = $port ?? 6379;
        $this->password = $password;
        $this->database = $database ?? 0;
        $this->keysPrefix = $keysPrefix;
    }

    public function configureSentinel($service, $sentinels)
    {
        $this->enableReplication = true;
        $this->service = $service;
        $this->sentinels = $sentinels;
    }

    public function getClient($database = null, $prefixRedisKeys = false): Client
    {
        $options = [];
        if ($prefixRedisKeys) {
            $options['prefix'] = $this->keysPrefix;
        }

        $options['parameters']['database'] = $database ?? $this->database;
        if ($this->password) {
            $options['parameters']['password'] = $this->password;
        }

        if ($this->enableReplication) {
            $options['replication'] = 'sentinel';
            $options['service'] = $this->service;
            return new Client($this->sentinels, $options);
        }

        return new Client([
            'scheme' => 'tcp',
            'host' => $this->host,
            'port' => $this->port,
        ], $options);
    }
}
