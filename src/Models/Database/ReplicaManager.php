<?php

namespace Crm\ApplicationModule\Models\Database;

use Crm\ApplicationModule\Database\DatabaseTransaction;
use Nette\Database\Explorer;

class ReplicaManager
{
    private $writeFlag = false;

    /** @var Explorer|null */
    private $selectedDatabase;

    public function __construct(
        private readonly Explorer $primaryDatabase,
        private readonly string $tableName,
        private readonly ?ReplicaConfig $replicaConfig,
        private readonly DatabaseTransaction $databaseTransaction,
    ) {
    }

    public function setWriteFlag()
    {
        $this->writeFlag = true;
        $this->selectedDatabase = null;
    }

    public function getDatabase($allowReplica): Explorer
    {
        if (!$allowReplica) {
            $this->setWriteFlag();
        }

        if ($this->databaseTransaction->isInTransaction()) {
            $this->setWriteFlag();
        }

        if ($this->selectedDatabase) {
            return $this->selectedDatabase;
        }

        if ($this->writeFlag || !isset($this->replicaConfig)) {
            // replica is not allowed or configured, use the default configured database
            $this->selectedDatabase = $this->primaryDatabase;
            return $this->selectedDatabase;
        }

        $replicas = $this->replicaConfig->getReplicas();
        if (!count($replicas) || !$this->replicaConfig->isTableAllowed($this->tableName)) {
            // if there's no replica, use primary database set for repository
            $this->selectedDatabase = $this->primaryDatabase;
            return $this->selectedDatabase;
        }

        $candidates = array_merge([$this->primaryDatabase], $replicas); // include primary database to the candidates
        $this->selectedDatabase = $candidates[
            array_rand($candidates)
        ];
        return $this->selectedDatabase;
    }

    /**
     * @return Explorer[]
     */
    public function getReplicas(): array
    {
        if (!isset($this->replicaConfig)) {
            return [];
        }
        return $this->replicaConfig->getReplicas();
    }
}
