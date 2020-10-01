<?php

namespace Crm\ApplicationModule\Hermes;

use Nette\Database\Context;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\MessageInterface;

class HeartbeatMysql implements HandlerInterface
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function handle(MessageInterface $message): bool
    {
        $this->database->query('SELECT "heartbeat"');
        return true;
    }
}
