<?php

namespace Crm\ApplicationModule\Graphs\Scale\Mysql;

use Nette\Database\Context;

class RangeScaleFactory
{
    private $database;

    public function __construct(Context $database)
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
