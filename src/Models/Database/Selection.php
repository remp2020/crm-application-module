<?php

namespace Crm\ApplicationModule\Models\Database;

use Nette\Database\ResultSet;

class Selection extends \Nette\Database\Table\Selection
{
    use DateFieldsProcessorTrait;

    /** @var ReplicaManager|null */
    private $replicaManager;

    private $enforcePrimaryDatabase = false;

    public function createSelectionInstance(string $table = null): self
    {
        return new self(
            $this->explorer,
            $this->conventions,
            $table ?: $this->name,
            $this->cache ? $this->cache->getStorage() : null,
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
            $this->enforcePrimaryDatabase = true;
        }
        return parent::update($data);
    }

    public function delete(): int
    {
        if ($this->replicaManager) {
            $this->enforcePrimaryDatabase = true;
        }
        return parent::delete();
    }

    public function insert(iterable $data): ActiveRow|array|int|bool
    {
        if ($this->replicaManager) {
            $this->enforcePrimaryDatabase = true;
        }
        return parent::insert($data);
    }

    public function query(string $query): ResultSet
    {
        if ($this->enforcePrimaryDatabase) {
            $explorer = $this->replicaManager->getDatabase(false);
            return $explorer->query($query, ...$this->sqlBuilder->getParameters());
        }
        return parent::query($query);
    }
}
