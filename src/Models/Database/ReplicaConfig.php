<?php

namespace Crm\ApplicationModule\Models\Database;

use Nette\Database\Explorer;

class ReplicaConfig
{
    /** @var Explorer[] */
    private $replicas = [];

    /** @var string[] */
    private $tables = [];

    public function addReplica(Explorer $replica)
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
