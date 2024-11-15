<?php declare(strict_types=1);

namespace Crm\ApplicationModule\Database;

trait DatabaseTransactionCallbacks
{
    /** @var callable[] */
    private array $onCommitCallbacks = [];

    /** @var callable[] */
    private array $onRollbackCallbacks = [];

    public function registerOnCommit(callable $callback): void
    {
        $this->onCommitCallbacks[] = $callback;
    }

    public function registerOnRollback(callable $callback): void
    {
        $this->onRollbackCallbacks[] = $callback;
    }

    private function fireCommitCallbacks(): void
    {
        foreach ($this->onCommitCallbacks as $callback) {
            $callback();
        }
    }

    private function fireRollbackCallbacks(): void
    {
        foreach ($this->onRollbackCallbacks as $callback) {
            $callback();
        }
    }
}
