<?php

namespace Crm\ApplicationModule\Event;

use Nette\Utils\Json;
use Predis\Client;

class RedisEventManager implements EventManagerInterface
{
    const EVENTS = 'events';

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

    public function push(Event $event)
    {
        $jsonValue = Json::encode($event->value);
        return $this->connect()->zadd(static::EVENTS, ["{$event->type}|{$jsonValue}" => $event->score]);
    }

    public function shift()
    {
        $events = $this->connect()->zrangebyscore(static::EVENTS, PHP_INT_MIN, PHP_INT_MAX, [
            'LIMIT' => [
                'OFFSET' => 0,
                'COUNT' => 1,
            ],
            'WITHSCORES' => true,
        ]);

        if (empty($events)) {
            return false;
        }

        foreach ($events as $rawEvent => $score) {
            $result = $this->connect()->zrem(static::EVENTS, $rawEvent);
            if (!$result) {
                return false;
            }
            list($type, $value) = explode("|", $rawEvent);
            return new Event($type, Json::decode($value, Json::FORCE_ARRAY), $score);
        }

        return false;
    }
}
