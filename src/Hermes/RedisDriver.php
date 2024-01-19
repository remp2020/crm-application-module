<?php

namespace Crm\ApplicationModule\Hermes;

use Closure;
use Crm\ApplicationModule\Repositories\HermesTasksRepository;
use Tomaj\Hermes\Dispatcher;
use Tomaj\Hermes\Driver\DriverInterface;
use Tomaj\Hermes\Driver\ShutdownTrait;
use Tomaj\Hermes\MessageInterface;
use Tomaj\Hermes\MessageSerializer;

class RedisDriver implements DriverInterface
{
    use ShutdownTrait;

    private $tasksRepository;

    private $redisTasksQueue;

    private $serializer;

    private $sleepTime = 1;

    public function __construct(HermesTasksRepository $tasksRepository, RedisTasksQueue $redisTasksQueue)
    {
        $this->tasksRepository = $tasksRepository;
        $this->redisTasksQueue = $redisTasksQueue;
        $this->serializer = new MessageSerializer();
    }

    public function send(MessageInterface $message, int $priority = Dispatcher::DEFAULT_PRIORITY): bool
    {
        $task = $this->serializer->serialize($message);
        $executeAt = 0;
        if ($message->getExecuteAt()) {
            $executeAt = $message->getExecuteAt();
        }

        $result = $this->redisTasksQueue->addTask($priority, $task, $executeAt);
        if ($result) {
            $this->redisTasksQueue->incrementType($message->getType());
        }

        return $result;
    }

    public function setupPriorityQueue(string $name, int $priority): void
    {
        $this->redisTasksQueue->setupPriorityQueue($name, $priority);
    }

    public function wait(Closure $callback, array $priorities): void
    {
        while (true) {
            $this->checkShutdown();

            $message = $this->redisTasksQueue->getTask();
            if ($message) {
                $hermesMessage = $this->serializer->unserialize($message[0]);
                $this->redisTasksQueue->decrementType($hermesMessage->getType());
                if ($hermesMessage->getExecuteAt() > time()) {
                    $this->send($hermesMessage);
                    continue;
                }

                $result = $callback($hermesMessage);
                if (!$result) {
                    $this->tasksRepository->add(
                        $hermesMessage,
                        HermesTasksRepository::STATE_ERROR
                    );
                }
            } else {
                sleep($this->sleepTime);
            }
        }
    }
}
