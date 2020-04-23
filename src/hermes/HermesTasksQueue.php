<?php

namespace Crm\ApplicationModule\Hermes;

use Crm\ApplicationModule\RedisClientFactory;
use Crm\ApplicationModule\RedisClientTrait;

class HermesTasksQueue
{
    use RedisClientTrait;

    const TASKS_KEY = 'hermes_tasks';
    const STATS_KEY = 'hermes_stats';

    public function __construct(RedisClientFactory $redisClientFactory)
    {
        $this->redisClientFactory = $redisClientFactory;
    }

    // Tasks
    public function addTask(string $task, float $executeAt)
    {
        return $this->redis()->zadd(static::TASKS_KEY, [$task => $executeAt]) > 0;
    }

    public function getTask()
    {
        $task = $this->redis()->zrangebyscore(static::TASKS_KEY, 0, time(), [
            'LIMIT' => [
                'OFFSET' => 0,
                'COUNT' => 1,
            ]
        ]);

        if (!empty($task)) {
            $result = $this->redis()->zrem(static::TASKS_KEY, $task);
            if ($result == 1) {
                return $task;
            }
        }

        return false;
    }

    public function getAllTask()
    {
        return $this->redis()->zrange(static::TASKS_KEY, 0, -1, ['withscores' => true]);
    }

    // Stats
    public function incrementType($type)
    {
        return $this->redis()->zincrby(static::STATS_KEY, 1, $type);
    }

    public function decrementType($type)
    {
        return $this->redis()->zincrby(static::STATS_KEY, -1, $type);
    }

    public function getTypeCounts()
    {
        return $this->redis()->zrange(static::STATS_KEY, 0, -1, ['withscores' => true]);
    }
}
