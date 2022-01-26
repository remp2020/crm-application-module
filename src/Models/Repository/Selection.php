<?php

namespace Crm\ApplicationModule;

use Crm\ApplicationModule\Repository\ReplicaManager;

class Selection extends \Nette\Database\Table\Selection
{
    use DateFieldsProcessorTrait;

    /** @var ReplicaManager|null */
    private $replicaManager;

    /**
     * @inheritdoc
     */
    public function createSelectionInstance($table = null)
    {
        return new self(
            $this->context,
            $this->conventions,
            $table ?: $this->name,
            $this->cache ? $this->cache->getStorage() : null
        );
    }

    /**
     * @inheritdoc
     */
    public function createRow(array $row)
    {
        return new ActiveRow($row, $this);
    }

    public function condition($condition, array $params, $tableChain = null)
    {
        $params = $this->processDateFields($params);
        parent::condition($condition, $params, $tableChain);
    }

    public function setReplicaManager(ReplicaManager $replicaManager)
    {
        $this->replicaManager = $replicaManager;
    }

    /**
     * update (and other write methods) needs to reinitialize context property to use primary database in order
     * to maintain integrity of the application.
     */
    public function update($data)
    {
        if ($this->replicaManager) {
            $this->context = $this->replicaManager->getDatabase(false);
        }
        return parent::update($data);
    }

    public function delete()
    {
        if ($this->replicaManager) {
            $this->context = $this->replicaManager->getDatabase(false);
        }
        return parent::delete();
    }

    public function insert($data)
    {
        if ($this->replicaManager) {
            $this->context = $this->replicaManager->getDatabase(false);
        }
        return parent::insert($data);
    }
}
