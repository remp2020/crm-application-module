<?php

namespace Crm\ApplicationModule\Graphs\Scale\Mysql;

use Crm\ApplicationModule\Graphs\Criteria;
use Nette\Database\Context;

class DayScale extends \Crm\ApplicationModule\Graphs\Scale\DayScale
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getDatabaseRangeData(Criteria $criteria)
    {
        $dbData = [];

        $this->database->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
calendar.day AS day,
calendar.month AS month,
calendar.year AS year,
{$criteria->getTableName()}.id,
calendar.date
FROM {$criteria->getTableName()}
INNER JOIN calendar ON date({$criteria->getTableName()}.{$criteria->getRangeStart()}) <= calendar.date
    AND date({$criteria->getTableName()}.{$criteria->getRangeEnd()}) >= calendar.date
    AND calendar.date >= '{$criteria->getStartDate()}'
    AND calendar.date <= '{$criteria->getEndDate()}'
    {$criteria->getJoin()}
WHERE
    {$criteria->getTableName()}.{$criteria->getRangeEnd()} >= '{$criteria->getStartDate('Y-m-d 00:00:00')}' 
AND {$criteria->getTableName()}.{$criteria->getRangeStart()} <= '{$criteria->getEndDate('Y-m-d 23:59:59')}'
	{$criteria->getWhere()}
GROUP BY calendar.date

		");

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }

            if (isset($row->name)) {
                $dbData[$row->name]["{$row->year}-{$row->month}-{$row->day}"] = $value;
            } else {
                $dbData[' ']["{$row->year}-{$row->month}-{$row->day}"] = $value;
            }
        }
        return $dbData;
    }

    public function getDatabaseData(Criteria $criteria, $tag)
    {
        $dbData = [];

        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
calendar.day AS day,
calendar.month AS month,
calendar.year AS year,
{$criteria->getTableName()}.id,
calendar.date
FROM {$criteria->getTableName()}
INNER JOIN calendar ON date({$criteria->getTableName()}.{$criteria->getTimeField()}) = calendar.date 
    AND calendar.date >= '{$criteria->getStartDate()}'
    AND calendar.date <= '{$criteria->getEndDate()}'
    {$criteria->getJoin()}
WHERE
	{$criteria->getTableName()}.{$criteria->getTimeField()} >= '{$criteria->getStartDate('Y-m-d 00:00:00')}' 
AND {$criteria->getTableName()}.{$criteria->getTimeField()} <= '{$criteria->getEndDate('Y-m-d 23:59:59')}'
	{$criteria->getWhere()}
GROUP BY calendar.date
		");

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            $dbData["{$row->year}-{$row->month}-{$row->day}"] = $value;
        }
        return $dbData;
    }

    public function getDatabaseSeriesData(Criteria $criteria)
    {
        $dbData = [];
//
//        $data = $this->database->query("
//            SELECT value,
//              calendar.day AS day,
//              calendar.month AS month,
//              calendar.year AS year
//            FROM calendar
//            LEFT JOIN dashboard_cache ON date(dashboard_cache.date) <= calendar.date AND date(dashboard_cache.date) >= calendar.date
//            WHERE tag = '{$tag}' AND
//            scale = 'day' AND
//            calendar.date <= '{$criteria->getEndDate()}' AND
//            calendar.date >= '{$criteria->getStartDate()}'
//        ");
//
//        $keys = $this->getKeys($criteria->getStart(), $criteria->getEnd());
//
//        $persist = [];
//        $dataCached = [];
//        foreach($data as $k =>$v) {
//            $dataCached[$k] = $v;
//        }
//        foreach($keys as $k => $v) {
//            if ( !isset($dataCached[$k])) {
//                $persist[$k] = $v;
//            }
//        }

        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
    calendar.day AS day,
    calendar.month AS month,
    calendar.year AS year,
    {$this->getSeries($criteria->getSeries())}
    {$criteria->getTableName()}.id
FROM {$criteria->getTableName()}
INNER JOIN calendar ON date({$criteria->getTableName()}.{$criteria->getTimeField()}) = calendar.date
    AND calendar.date >= '{$criteria->getStartDate()}'
	AND calendar.date <= '{$criteria->getEndDate()}'
    {$criteria->getJoin()}
WHERE
    {$criteria->getTableName()}.{$criteria->getTimeField()} >= '{$criteria->getStartDate('Y-m-d 00:00:00')}'  
AND {$criteria->getTableName()}.{$criteria->getTimeField()} <= '{$criteria->getEndDate('Y-m-d 23:59:59')}' 	
	{$criteria->getWhere()}
GROUP BY calendar.year, calendar.month, calendar.day " . $this->getGroupBy($criteria->getGroupBy()) . '
		');

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }

            if (isset($row->name)) {
                $dbData[$row->name]["{$row->year}-{$row->month}-{$row->day}"] = $value;
            } else {
                $dbData[' ']["{$row->year}-{$row->month}-{$row->day}"] = $value;
            }
        }
        return $dbData;
    }
}
