<?php

namespace Crm\ApplicationModule;

class Selection extends \Nette\Database\Table\Selection
{
    use DateFieldsProcessorTrait;

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
}
