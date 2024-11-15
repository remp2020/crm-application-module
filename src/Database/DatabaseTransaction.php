<?php declare(strict_types=1);

namespace Crm\ApplicationModule\Database;

use Nette\Database\Explorer;
use Throwable;

/**
 * Database transaction wrapper that allows for nested transactions. Reason why we need this even though Nette\Database
 * supports nested transactions through \Nette\Database\Connection API is that we need to handle nested
 * transactions even within `beginTransaction()`, `commit()` and `rollBack()` calls.
 */
class DatabaseTransaction
{
    use DatabaseTransactionCallbacks;

    private int $depth = 0;

    public function __construct(
        private readonly Explorer $explorer,
    ) {
    }

    public function start(): void
    {
        if ($this->depth === 0) {
            $this->explorer->beginTransaction();
        }

        $this->depth++;
    }

    public function commit(): void
    {
        $this->depth--;

        if ($this->depth === 0) {
            $this->explorer->commit();
            $this->fireCommitCallbacks();
        }
    }

    public function rollback(): void
    {
        $this->depth--;

        if ($this->depth === 0) {
            $this->explorer->rollBack();
            $this->fireRollbackCallbacks();
        }
    }

    public function wrap(callable $callback): mixed
    {
        $this->start();

        try {
            $result = $callback();
            $this->commit();

            return $result;
        } catch (Throwable $e) {
            $this->rollback();

            throw $e;
        }
    }

    public function isInTransaction(): bool
    {
        return $this->depth > 0;
    }
}
