<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Models\Database\NewTableDataMigrationTrait;
use Crm\ApplicationModule\Models\Database\Repository;
use Crm\ApplicationModule\Models\Database\Selection;
use Nette\Database\Explorer;
use Nette\Security\UserStorage;

class AuditLogRepository extends Repository
{
    use NewTableDataMigrationTrait;

    protected $tableName = 'audit_logs';

    const OPERATION_CREATE = 'create';
    const OPERATION_READ = 'read';
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';

    public function __construct(Explorer $database, protected UserStorage $userStorage)
    {
        parent::__construct($database);
    }

    final public function add($operation, $tableName, $signature, $data = [])
    {
        [$isAuthenticated, $identity, $reason] = $this->userStorage->getState();
        $userId = $identity ? $identity->getId() : null;

        return $this->insert([
            'operation' => $operation,
            'user_id' => $userId,
            'table_name' => $tableName,
            'signature' => $signature,
            'data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    final public function getByTableAndSignature(string $tableName, string $signature): Selection
    {
        return $this->getTable()->where([
            'table_name' => $tableName,
            'signature' => $signature,
        ]);
    }

    final public function getByTableAndSignatures(string $tableName, array $signatures): Selection
    {
        return $this->getTable()->where([
            'table_name' => $tableName,
            'signature IN (?)' => array_map('strval', $signatures),
        ]);
    }
}
