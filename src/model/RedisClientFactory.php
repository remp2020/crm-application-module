<?php

namespace Crm\ApplicationModule;

use Predis\Client;

class RedisClientFactory
{
    private $host;
    private $port;
    private $password;
    private $database;
    private $keysPrefix;

    public function __construct($host, $port, $password, $database, ?string $keysPrefix = null)
    {
        $this->host = $host ?? '127.0.0.1';
        $this->port = $port ?? 6379;
        $this->password = $password;
        $this->database = $database ?? 0;
        $this->keysPrefix = $keysPrefix;
    }

    public function getClient($database = null, $prefixRedisKeys = false): Client
    {
        $options = [];
        if ($prefixRedisKeys) {
            $options['prefix'] = $this->keysPrefix;
        }

        $client = new Client([
            'scheme' => 'tcp',
            'host' => $this->host,
            'port' => $this->port
        ], $options);

        if ($this->password) {
            $client->auth($this->password);
        }

        $client->select($database ?? $this->database);
        return $client;
    }
}
