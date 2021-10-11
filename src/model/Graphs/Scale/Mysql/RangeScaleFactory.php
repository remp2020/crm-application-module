<?php

namespace Crm\ApplicationModule\Graphs\Scale\Mysql;

use Nette\Database\Explorer;

class RangeScaleFactory
{
    private $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function create($group)
    {
        switch ($group) {
            case 'day':
                return new DayScale($this->database);
            case 'week':
                return new WeekScale($this->database);
            case 'month':
                return new MonthScale($this->database);
            case 'year':
                return new YearScale($this->database);
        }
        throw new \Exception("unhandled group scale [{$group}]");
    }
}
