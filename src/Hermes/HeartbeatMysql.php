<?php

namespace Crm\ApplicationModule\Hermes;

use Crm\ApplicationModule\Repositories\ConfigsRepository;
use Nette\Database\Explorer;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\MessageInterface;

class HeartbeatMysql implements HandlerInterface
{
    private $database;

    private $configsRepository;

    public function __construct(Explorer $database, ConfigsRepository $configsRepository)
    {
        $this->database = $database;
        $this->configsRepository = $configsRepository;
    }

    public function handle(MessageInterface $message): bool
    {
        $this->database->query('SELECT "heartbeat"');

        foreach ($this->configsRepository->getReplicaManager()->getReplicas() as $replica) {
            $replica->query('SELECT "heartbeat"');
        }

        return true;
    }
}
