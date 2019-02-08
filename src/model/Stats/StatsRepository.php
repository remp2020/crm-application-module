<?php

namespace Crm\ApplicationModule\Stats;

use Crm\ApplicationModule\Repository;
use Nette\Database\Context;

class StatsRepository extends Repository
{
    protected $tableName = 'stats';

    public function __construct(
        Context $database
    ) {
        parent::__construct($database);
    }

    public function loadByKey($key)
    {
        return $this->getTable()->where('key', $key)->fetch();
    }

    public static function insertOrUpdateQuery($key, $valueQuery)
    {
        return "INSERT INTO stats (`key`, `value`) VALUES ('$key', ($valueQuery)) ON DUPLICATE KEY UPDATE value=VALUES(value);";
    }
}
