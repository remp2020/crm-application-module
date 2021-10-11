<?php

namespace Crm\ApplicationModule\Repository;

use Nette\Database\Explorer;

/**
 * @property Explorer $database
 * @property string $tableName
 */
trait ReplicaTrait
{
    /** @var ReplicaConfig|null */
    private $replicaConfig;

    /** @var ReplicaManager */
    private $replicaManager;

    public function setReplicaConfig(ReplicaConfig $replicaConfig)
    {
        $this->replicaConfig = $replicaConfig;
    }

    public function getReplicaManager(): ReplicaManager
    {
        if (!isset($this->replicaManager)) {
            $this->replicaManager = new ReplicaManager(
                $this->database,
                $this->tableName,
                $this->replicaConfig
            );
        }
        return $this->replicaManager;
    }

    /**
     * allowReplica needs to be false by default, to avoid scenarios like this to go to readonly instance:
     *     $this->getDatabase()->query("UPDATE table SET foo = bar")
     *
     * Only repository requests that we control (or explicit requests which know that they won't write anything)
     * should be able to receive replica.
     */
    public function getDatabase($allowReplica = false): Explorer
    {
        return $this->getReplicaManager()->getDatabase($allowReplica);
    }
}
