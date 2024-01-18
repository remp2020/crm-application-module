<?php

namespace Crm\ApplicationModule;

use Crm\ApplicationModule\Repository\ReplicaManager;

class Selection extends \Nette\Database\Table\Selection
{
    use DateFieldsProcessorTrait;

    /** @var ReplicaManager|null */
    private $replicaManager;

    public function createSelectionInstance(string $table = null): self
    {
        return new self(
            $this->explorer,
            $this->conventions,
            $table ?: $this->name,
            $this->cache ? $this->cache->getStorage() : null
        );
    }

    public function createRow(array $row): ActiveRow
    {
        return new ActiveRow($row, $this);
    }

    public function condition($condition, array $params, $tableChain = null): void
    {
        $params = $this->processDateFields($params);
        parent::condition($condition, $params, $tableChain);
    }

    public function setReplicaManager(ReplicaManager $replicaManager): void
    {
        $this->replicaManager = $replicaManager;
    }

    /**
     * update (and other write methods) needs to reinitialize context property to use primary database in order
     * to maintain integrity of the application.
     */
    public function update(iterable $data): int
    {
        if ($this->replicaManager) {
            $this->explorer = $this->replicaManager->getDatabase(false);
        }
        return parent::update($data);
    }

    public function delete(): int
    {
        if ($this->replicaManager) {
            $this->explorer = $this->replicaManager->getDatabase(false);
        }
        return parent::delete();
    }

    public function insert(iterable $data): ActiveRow|array|int|bool
    {
        if ($this->replicaManager) {
            $this->explorer = $this->replicaManager->getDatabase(false);
        }
        return parent::insert($data);
    }
}
