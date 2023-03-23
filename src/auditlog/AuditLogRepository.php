<?php

namespace Crm\ApplicationModule\Repository;

use Crm\ApplicationModule\Repository;
use Crm\ApplicationModule\Selection;
use Nette\Database\Explorer;
use Nette\Security\IUserStorage;

class AuditLogRepository extends Repository
{
    protected $tableName = 'audit_logs';

    protected $userStorage;

    const OPERATION_CREATE = 'create';
    const OPERATION_READ = 'read';
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';

    public function __construct(Explorer $database, IUserStorage $userStorage)
    {
        parent::__construct($database);
        $this->database = $database;
        $this->userStorage = $userStorage;
    }

    final public function add($operation, $tableName, $signature, $data = [])
    {
        $identity = $this->userStorage->getIdentity();
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
            'signature' => $signature
        ]);
    }
}
