<?php declare(strict_types=1);

namespace Crm\ApplicationModule\Models\Database;

use Crm\ApplicationModule\Database\DatabaseTransaction;
use Nette\Database\Explorer;
use RuntimeException;

/**
 * @property Explorer $database
 */
trait DatabaseTransactionTrait
{
    private DatabaseTransaction $databaseTransaction;

    public function setTransaction(DatabaseTransaction $databaseTransaction): void
    {
        $this->databaseTransaction = $databaseTransaction;
    }

    public function getTransaction(): DatabaseTransaction
    {
        if (!isset($this->databaseTransaction)) {
            throw new RuntimeException('DatabaseTransaction must be setup via config to work properly.');
        }

        return $this->databaseTransaction;
    }
}
