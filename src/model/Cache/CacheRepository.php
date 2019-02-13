<?php

namespace Crm\ApplicationModule\Cache;

use Crm\ApplicationModule\Repository;
use Nette\Database\Context;
use Nette\Utils\DateTime;

class CacheRepository extends Repository
{
    protected $tableName = 'cache';

    public function __construct(
        Context $database
    ) {
        parent::__construct($database);
    }

    /**
     * Retrieve value either from cache or using $getValue callable (and subsequently cache it in DB)
     * @param               $key
     * @param callable      $getValue
     * @param DateTime|null $notOlderThan
     * @param bool          $forceUpdate
     *
     * @return mixed|\Nette\Database\Table\ActiveRow
     */
    public function loadByKeyAndUpdate($key, callable $getValue, DateTime $notOlderThan = null, $forceUpdate = false)
    {
        if (!$forceUpdate) {
            $stat = $this->loadByKey($key, $notOlderThan);
            if ($stat) {
                return $stat->value;
            }
        }

        $value = $getValue();
        $this->updateKey($key, $value);
        return $value;
    }

    public function loadByKey($key, DateTime $notOlderThan = null)
    {
        $q = $this->getTable()
            ->where('key', $key);

        if ($notOlderThan) {
            $q->where('updated_at >= ?', $notOlderThan);
        }

        return $q->fetch();
    }

    public function updateKey($key, $value)
    {
        $this->getDatabase()->query(
            'INSERT INTO cache (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE value=VALUES(value), updated_at=NOW()',
            $key,
            $value
        );
    }
}
