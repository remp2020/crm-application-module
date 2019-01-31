<?php

namespace Crm\ApplicationModule\Graphs\Scale\Mysql;

use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\Scale\ScaleBase;
use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;
use DateTime;
use Nette\Database\Context;

class WeekScale extends ScaleBase implements ScaleInterface
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getKeys($start, $end)
    {
        $actual = new DateTime(date('Y-m-d', strtotime($start)));
        $endDateTime = new DateTime(date('Y-m-d', strtotime($end)));

        $diff = $actual->diff($endDateTime);
        $days = intval($diff->format('%a'));
        $weeks = ceil($days / 7);
        $result = [];
        $actual->setISODate($actual->format('Y'), $actual->format('W'));
        $result[] = $actual->format('Y-m-d');
        for ($i = 0; $i < $weeks; $i++) {
            $actual = $actual->modify('+1 week');
            $result[] = $actual->format('Y-m-d');
        }
        $finalResult = [];
        foreach ($result as $week) {
            $finalResult[$week] = 0;
        }

        return $finalResult;
    }

    public function getDatabaseRangeData(Criteria $criteria)
    {
        $dbData = [];

        $where = '';
        if ($criteria->getWhere()) {
            $where = 'WHERE 1=1 ' . $criteria->getWhere();
        }

        $res = $this->database->query("SELECT
time_series.time_key,time_series.year,time_series.month,time_series.week,
  {$criteria->getTableName()}.id,{$criteria->getValueField()} AS value
FROM
( SELECT
    calendar.month, calendar.year, calendar.date,calendar.week, CONCAT(calendar.year, '-', calendar.week) AS time_key
  FROM calendar
  WHERE
    calendar.date >= '{$criteria->getStartDate()}' AND
    calendar.date <  '{$criteria->getEndDate()}'
  GROUP BY calendar.year,calendar.week
) AS time_series

LEFT JOIN {$criteria->getTableName()} ON
  date({$criteria->getTableName()}.{$criteria->getRangeStart()}) <= time_series.date AND
  date({$criteria->getTableName()}.{$criteria->getRangeEnd()}) >= time_series.date

{$criteria->getJoin()}

{$where}

GROUP BY time_series.time_key
");

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            $date = new \DateTime();
            $date->setISODate($row->year, $row->week);
            if (isset($row->name)) {
                $dbData[$row->name][$date->format('Y-m-d')] = $value;
            } else {
                $dbData[' '][$date->format('Y-m-d')] = $value;
            }
        }
        return $dbData;
    }

    public function getDatabaseData(Criteria $criteria, $tag)
    {
        $dbData = [];

        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
calendar.week AS week,
calendar.month AS month,
calendar.year AS year,
{$criteria->getTableName()}.id
FROM {$criteria->getTableName()}
INNER JOIN calendar ON date({$criteria->getTableName()}.{$criteria->getTimeField()}) = calendar.date
    AND calendar.date >= '{$criteria->getStartDate()}'
	AND calendar.date <= '{$criteria->getEndDate()}'
    {$criteria->getJoin()}
WHERE
    {$criteria->getTableName()}.{$criteria->getTimeField()} >= '{$criteria->getStartDate()}' 
AND {$criteria->getTableName()}.{$criteria->getTimeField()} <= '{$criteria->getEndDate()}'	
	{$criteria->getWhere()}
GROUP BY calendar.year,calendar.month,calendar.week

		");

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            $dbData["{$row->year}-{$row->month}-{$row->week}"] = $value;
        }

        return $dbData;
    }

    //pridat do vsetkych queries ?
    public function getDatabaseSeriesData(Criteria $criteria)
    {
        $dbData = [];

        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
calendar.week AS week,
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
    {$criteria->getTableName()}.{$criteria->getTimeField()} >= '{$criteria->getStartDate()}' 
AND {$criteria->getTableName()}.{$criteria->getTimeField()} <= '{$criteria->getEndDate()}'
	{$criteria->getWhere()}
GROUP BY calendar.year,calendar.month,calendar.week" . $this->getGroupBy($criteria->getGroupBy()) . '
		');

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            $date = new \DateTime();
            $date->setISODate($row->year, $row->week);
            if (isset($row->name)) {
                $dbData[$row->name][$date->format('Y-m-d')] = $value;
            } else {
                $dbData[' '][$date->format('Y-m-d')] = $value;
            }
        }
        return $dbData;
    }
}
