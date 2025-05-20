<?php

namespace Crm\ApplicationModule\Hermes;

use Crm\ApplicationModule\Models\Redis\RedisClientFactory;
use Crm\ApplicationModule\Models\Redis\RedisClientTrait;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Tomaj\Hermes\Driver\UnknownPriorityException;

class RedisTasksQueue
{
    use RedisClientTrait;

    const TASKS_KEY = 'hermes_tasks';
    const STATS_KEY = 'hermes_stats';

    /** @var array<int, string>  */
    private $queues = [];

    public function __construct(RedisClientFactory $redisClientFactory)
    {
        $this->redisClientFactory = $redisClientFactory;
    }

    public function setupPriorityQueue(string $name, int $priority): void
    {
        $this->queues[$priority] = $name;
        krsort($this->queues);
    }

    private function getKey(int $priority): string
    {
        if (!isset($this->queues[$priority])) {
            throw new UnknownPriorityException("Unknown priority {$priority}");
        }
        return $this->queues[$priority];
    }

    // Tasks
    public function addTask(int $priority, string $task, float $executeAt)
    {
        return $this->redis()->zadd($this->getKey($priority), [$task => $executeAt]) > 0;
    }

    public function getTask(array $priorities = [])
    {
        foreach ($this->queues as $priority => $name) {
            if (count($priorities) > 0 && !in_array($priority, $priorities, true)) {
                continue;
            }

            $key = $this->getKey($priority);

            $task = $this->redis()->zrangebyscore($key, 0, time(), [
                'LIMIT' => [
                    'OFFSET' => 0,
                    'COUNT' => 1,
                ],
            ]);

            if (!empty($task)) {
                $result = $this->redis()->zrem($key, $task);
                if ($result == 1) {
                    return $task;
                }
            }
        }

        return false;
    }

    public function getAllTask($limit = -1)
    {
        $result = [];
        foreach ($this->queues as $priority => $name) {
            $data = $this->redis()->zrange($name, 0, $limit, ['withscores' => true]);
            foreach ($data as $r => $processedTime) {
                $message = Json::decode($r);
                $message->priority = $priority;
                $message->processedTime = $processedTime ? DateTime::createFromFormat('U.u', number_format($processedTime, 6, '.', '')) : null;
                $result[] = $message;
            }

            if ($limit != -1 && count($result) >= $limit) {
                break;
            }
        }
        return $result;
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
