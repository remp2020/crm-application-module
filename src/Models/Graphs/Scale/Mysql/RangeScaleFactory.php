<?php

namespace Crm\ApplicationModule\Models\Graphs\Scale\Mysql;

use Crm\ApplicationModule\Graphs\ScaleFactory;
use Nette\Database\Explorer;

class RangeScaleFactory
{
    public const PROVIDER_MYSQL = 'mysql';

    private Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function create($group)
    {
        switch ($group) {
            case ScaleFactory::RANGE_DAY:
                return new DayScale($this->database);
            case ScaleFactory::RANGE_WEEK:
                return new WeekScale($this->database);
            case ScaleFactory::RANGE_MONTH:
                return new MonthScale($this->database);
            case ScaleFactory::RANGE_YEAR:
                return new YearScale($this->database);
        }
        throw new \Exception("unhandled group scale [{$group}]");
    }
}
