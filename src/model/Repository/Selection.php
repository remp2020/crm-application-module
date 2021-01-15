<?php

namespace Crm\ApplicationModule;

use Nette\Caching\IStorage;
use Nette\Database\Context;
use Nette\Database\IConventions;

class Selection extends \Nette\Database\Table\Selection
{
    use DateFieldsProcessorTrait;

    /**
     * @inheritdoc
     */
    public function __construct(Context $context, IConventions $conventions, $tableName, IStorage $cacheStorage = null)
    {
        parent::__construct($context, $conventions, $tableName, $cacheStorage);
    }

    /**
     * @inheritdoc
     */
    public function createSelectionInstance($table = null)
    {
        return new static($this->context, $this->conventions, $table ?: $this->name, $this->cache ? $this->cache->getStorage() : null);
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
