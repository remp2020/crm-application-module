<?php

namespace Crm\ApplicationModule\Models\User;

use Crm\ApplicationModule\Models\Redis\RedisClientFactory;
use Crm\ApplicationModule\Models\Redis\RedisClientTrait;

class RedisUserDataStorage implements UserDataStorageInterface
{
    use RedisClientTrait;

    private $userDataKey = 'user_data';

    public function __construct(RedisClientFactory $redisClientFactory)
    {
        $this->redisClientFactory = $redisClientFactory;
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

    public function iterateTokens(callable $callback)
    {
        $cursor = null;

        do {
            $response = $this->redis()->hscan($this->userDataKey, $cursor);
            $cursor = $response[0];
            $items = $response[1];
            foreach ($items as $accessToken => $data) {
                $callback($accessToken, $data);
            }
        } while ($cursor);
    }
}
