<?php

namespace Crm\ApplicationModule\Models\Graphs\Scale\Mysql;

use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;
use Nette\Database\Explorer;

class WeekScale extends \Crm\ApplicationModule\Graphs\Scale\WeekScale implements ScaleInterface
{
    private $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
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
    calendar.month,
    calendar.year, 
    calendar.date, 
    WEEK(calendar.date, 3) as week, 
    YEARWEEK(calendar.date, 3) AS time_key
  FROM calendar
  WHERE
    calendar.date >= '{$criteria->getStartDate()}' AND
    calendar.date <  '{$criteria->getEndDate()}'
  GROUP BY YEARWEEK(calendar.date, 3)
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

        // NOTE: function will for some dates return an incorrect value in the month column.
        //       For example, the date 2019-30-12 will return:
        //       - week: 1
        //       - month: 12
        //       - year: 2020
        //
        //       The reason for this behavior can be found in the explainer comment of the function `getDatabaseSeriesData` found bellow.
        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
WEEK(calendar.date, 3) AS week,
calendar.month AS month,
YEARWEEK(calendar.date, 3) DIV 100 AS year,
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
GROUP BY YEARWEEK(calendar.date, 3)

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

        
        // The year column has to be calculated with the expression YEARWEEK(calendar.date, 3) DIV 100.
        // We do this because, for example, the date 2019-30-12 is the 1st week of the year 2020.
        // This means that if we were to return the year normally, the result would be:
        // - year 2019
        // - month 12
        // - week 1
        //
        // This is technically correct but obviously not what we want, we want the year the week is a part of.
        
        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
WEEK(calendar.date, 3) AS week,
YEARWEEK(calendar.date, 3) DIV 100 AS year,
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
GROUP BY YEARWEEK(calendar.date, 3)" . $this->getGroupBy($criteria->getGroupBy()) . '
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
