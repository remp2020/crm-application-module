<?php

namespace Crm\ApplicationModule\Stats;

use Crm\ApplicationModule\Repository;
use Nette\Database\Context;
use Nette\Utils\DateTime;

class StatsRepository extends Repository
{
    protected $tableName = 'stats';

    public function __construct(
        Context $database
    ) {
        parent::__construct($database);
    }


    /**
     * Retrieve cached value either from Stats table or using $getValue callable (and subsequently cache it in DB)
     * @param               $key
     * @param callable      $getValue
     * @param DateTime|null $notOlderThan
     * @param bool          $forceUpdate
     *
     * @return mixed|\Nette\Database\Table\ActiveRow
     */
    public function loadByKeyAndUpdateCache($key, callable $getValue, DateTime $notOlderThan = null, $forceUpdate = false)
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
            'INSERT INTO stats (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value)',
            $key,
            $value
        );
    }

    public static function insertOrUpdateQuery($key, $valueQuery)
    {
        return "INSERT INTO stats (`key`, `value`) VALUES ('$key', ($valueQuery)) ON DUPLICATE KEY UPDATE value=VALUES(value);";
    }
}
