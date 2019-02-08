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
}
