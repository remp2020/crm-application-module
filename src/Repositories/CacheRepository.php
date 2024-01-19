<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Repository;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class CacheRepository extends Repository
{
    const REFRESH_TIME_5_MINUTES = '-5 minutes';
    const REFRESH_TIME_1_HOUR = '-1 hour';

    protected $tableName = 'cache';

    public function __construct(
        Explorer $database
    ) {
        parent::__construct($database);
    }

    /**
     * Retrieve value either from cache or using $getValue callable(and subsequently cache it in DB)
     * @param               $key
     * @param callable      $getValue
     * @param DateTime|null $notOlderThan
     * @param bool          $forceUpdate
     *
     * @return mixed|ActiveRow
     */
    final public function loadAndUpdate($key, callable $getValue, DateTime $notOlderThan = null, $forceUpdate = false)
    {
        if (!$forceUpdate) {
            $stat = $this->load($key, $notOlderThan);
            if ($stat) {
                return $stat->value;
            }
        }

        $value = $getValue();
        if ($value === null) {
            $value = 0;
        }
        $this->updateKey($key, $value);
        return $value;
    }

    final public function load($key, DateTime $notOlderThan = null)
    {
        $q = $this->getTable()
            ->where('key', $key);

        if ($notOlderThan) {
            $q->where('updated_at >= ?', $notOlderThan);
        }

        return $q->fetch();
    }

    final public function remove(string $key): bool
    {
        $row = $this->getTable()->where('key', $key)->fetch();
        if (!$row) {
            return false;
        }
        return $this->delete($row);
    }

    final public function updateKey($key, $value)
    {
        $now = new DateTime();
        $this->getDatabase()->query(
            'INSERT INTO cache (`key`, `value`, `updated_at`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value), updated_at=?',
            $key,
            $value,
            $now,
            $now
        );
    }
}
