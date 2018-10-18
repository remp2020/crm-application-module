<?php

namespace Crm\ApplicationModule\User;

use Predis\Client;

class RedisUserDataStorage implements UserDataStorageInterface
{
    private $redis;

    private $host;

    private $port;

    private $db;

    private $userDataKey = 'user_data';

    public function __construct($host = '127.0.0.1', $port = 6379, $db = 0)
    {
        $this->host = $host ?? '127.0.0.1';
        $this->port = $port ?? 6379;
        $this->db = $db;
    }

    public function load($token)
    {
        return $this->redis()->hget($this->userDataKey, $token);
    }

    public function multiLoad(array $tokens)
    {
        if (empty($tokens)) {
            return [];
        }
        $data = $this->redis()->hmget($this->userDataKey, $tokens);
        $result = [];
        for ($i = 0; $i < count($data); $i++) {
            $result[$tokens[$i]] = $data[$i];
        }
        return $result;
    }

    public function store($token, $data)
    {
        return $this->redis()->hset($this->userDataKey, $token, $data);
    }

    public function multiStore(array $tokens, $data)
    {
        if (!$data) {
            return false;
        }
        if (count($tokens) == 0) {
            return false;
        }
        $newData = [];
        foreach ($tokens as $token) {
            $newData[$token] = $data;
        }
        return $this->redis()->hmset($this->userDataKey, $newData);
    }

    public function remove($token)
    {
        return $this->redis()->hdel($this->userDataKey, $token);
    }

    public function multiRemove(array $tokens)
    {
        return $this->redis()->hdel($this->userDataKey, $tokens);
    }

    private function redis()
    {
        if (!$this->redis) {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host'   => $this->host,
                'port'   => $this->port,
            ]);
        }
        return $this->redis;
    }
}
