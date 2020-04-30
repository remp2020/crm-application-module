<?php

namespace Crm\ApplicationModule;

use Predis\Client;

class RedisClientFactory
{
    private $host;
    private $port;
    private $password;
    private $database;

    public function __construct($host, $port, $password, $database)
    {
        $this->host = $host ?? '127.0.0.1';
        $this->port = $port ?? 6379;
        $this->password = $password;
        $this->database = $database ?? 0;
    }

    public function getClient($database = null): Client
    {
        $client = new Client([
            'scheme' => 'tcp',
            'host' => $this->host,
            'port' => $this->port
        ]);

        if ($this->password) {
            $client->auth($this->password);
        }

        $client->select($database ?? $this->database);
        return $client;
    }
}
