<?php declare(strict_types=1);

namespace Crm\ApplicationModule\Hermes;

use Crm\ApplicationModule\Database\DatabaseTransaction;
use Psr\Log\LoggerInterface;
use Tomaj\Hermes\Driver\DriverInterface;
use Tomaj\Hermes\Emitter;
use Tomaj\Hermes\EmitterInterface;

class TransactionEmitter extends Emitter
{
    private array $delayedMessages = [];

    public function __construct(
        private readonly DatabaseTransaction $databaseTransaction,
        DriverInterface $driver,
        LoggerInterface $logger = null,
    ) {
        parent::__construct($driver, $logger);

        $this->databaseTransaction->registerOnCommit(fn () => $this->onDatabaseCommit());
        $this->databaseTransaction->registerOnRollback(fn () => $this->onDatabaseRollback());
    }

    public function emit(...$args): EmitterInterface
    {
        if ($this->databaseTransaction->isInTransaction()) {
            $this->delayedMessages[] = $args;
            return $this;
        }

        return parent::emit(...$args);
    }

    private function onDatabaseCommit(): void
    {
        foreach ($this->delayedMessages as $args) {
            parent::emit(...$args);
        }

        $this->delayedMessages = [];
    }

    private function onDatabaseRollback(): void
    {
        $this->delayedMessages = [];
    }
}
