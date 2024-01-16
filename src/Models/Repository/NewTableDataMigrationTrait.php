<?php

namespace Remp\MailerModule\Repositories;

use Crm\ApplicationModule\RedisClientFactory;
use Crm\ApplicationModule\RedisClientTrait;
use Crm\ApplicationModule\Selection;
use Nette\Database\Table\ActiveRow;

/**
 * @internal
 */
trait NewTableDataMigrationTrait
{
    use RedisClientTrait;

    protected ?string $newTableName = null;

    protected ?string $newTableDataMigrationIsRunningFlag = null;

    public function setNewTableName(string $table): void
    {
        $this->newTableName = $table;
    }

    public function setNewTableDataMigrationIsRunningFlag(string $flag): void
    {
        $this->newTableDataMigrationIsRunningFlag = $flag;
    }

    public function setRedisClientFactory(RedisClientFactory $redisClientFactory): void
    {
        $this->redisClientFactory = $redisClientFactory;
    }

    public function getNewTable(): Selection
    {
        return new Selection($this->database, $this->database->getConventions(), $this->newTableName, $this->cacheStorage);
    }

    public function newTableDataMigrationIsRunning(): bool
    {
        return (bool) $this->redis()->exists($this->newTableDataMigrationIsRunningFlag);
    }

    public function insert($data)
    {
        $result = parent::insert($data);
        if ($this->newTableDataMigrationIsRunning()) {
            $this->getNewTable()->insert($result->toArray());
        }
        return $result;
    }

    public function update(ActiveRow &$row, $data)
    {
        $result = parent::update($row, $data);
        if ($this->newTableDataMigrationIsRunning()) {
            $this->getNewTable()->where('id', $row->id)->update($data);
        }
        return $result;
    }
}
