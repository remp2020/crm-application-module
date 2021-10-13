<?php

namespace Crm\ApplicationModule\Repository;

use Nette\Database\Context;

class ReplicaConfig
{
    /** @var Context[] */
    private $replicas = [];

    /** @var string[] */
    private $tables = [];

    public function addReplica(Context $replica)
    {
        $this->replicas[] = $replica;
    }

    public function addTable(string $table)
    {
        $this->tables[$table] = true;
    }

    public function getReplicas(): array
    {
        return $this->replicas;
    }

    public function isTableAllowed(string $tableName): bool
    {
        return isset($this->tables[$tableName]);
    }
}
