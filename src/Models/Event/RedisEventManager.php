<?php

namespace Crm\ApplicationModule\Event;

use Crm\ApplicationModule\RedisClientFactory;
use Crm\ApplicationModule\RedisClientTrait;
use Nette\Utils\Json;

class RedisEventManager implements EventManagerInterface
{
    use RedisClientTrait;

    const EVENTS = 'events';

    public function __construct(RedisClientFactory $redisClientFactory)
    {
        $this->redisClientFactory = $redisClientFactory;
    }

    public function push(Event $event): int
    {
        $jsonValue = Json::encode($event->value);
        return $this->redis()->zadd(static::EVENTS, ["{$event->type}|{$jsonValue}" => $event->score]);
    }

    public function shift(): ?Event
    {
        $events = $this->redis()->zrangebyscore(static::EVENTS, PHP_INT_MIN, PHP_INT_MAX, [
            'LIMIT' => [
                'OFFSET' => 0,
                'COUNT' => 1,
            ],
            'WITHSCORES' => true,
        ]);

        if (empty($events)) {
            return null;
        }

        foreach ($events as $rawEvent => $score) {
            $result = $this->redis()->zrem(static::EVENTS, $rawEvent);
            if (!$result) {
                return null;
            }
            list($type, $value) = explode("|", $rawEvent);
            return new Event($type, Json::decode($value, Json::FORCE_ARRAY), $score);
        }

        return null;
    }
}
