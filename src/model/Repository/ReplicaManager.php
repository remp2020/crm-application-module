<?php

namespace Crm\ApplicationModule\Repository;

use Nette\Database\Context;

class ReplicaManager
{
    private $primaryDatabase;

    private $tableName;

    /** @var ReplicaConfig|null */
    private $replicaConfig;

    private $writeFlag = false;

    /** @var Context */
    private $selectedDatabase;

    public function __construct(Context $primaryDatabase, string $tableName, ?ReplicaConfig $replicaConfig)
    {
        $this->primaryDatabase = $primaryDatabase;
        $this->tableName = $tableName;
        $this->replicaConfig = $replicaConfig;
    }

    public function setWriteFlag()
    {
        $this->writeFlag = true;
        $this->selectedDatabase = null;
    }

    public function getDatabase($allowReplica): Context
    {
        if (!$allowReplica) {
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
     * @return Context[]
     */
    public function getReplicas(): array
    {
        if (!isset($this->replicaConfig)) {
            return [];
        }
        return $this->replicaConfig->getReplicas();
    }
}
