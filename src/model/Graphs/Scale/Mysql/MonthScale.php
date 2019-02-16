<?php

namespace Crm\ApplicationModule\Graphs\Scale\Mysql;

use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;
use Nette\Database\Context;

class MonthScale extends \Crm\ApplicationModule\Graphs\Scale\MonthScale implements ScaleInterface
{
    private $database;

    public function __construct(Context $database)
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
time_series.time_key,time_series.year,time_series.month,
  {$criteria->getTableName()}.id,{$criteria->getValueField()} AS value FROM
( SELECT calendar.month, calendar.year, calendar.date,(CONCAT(calendar.year, '-', calendar.month)) AS time_key
  FROM calendar
  WHERE
    calendar.date >= '{$criteria->getStartDate()}' AND
    calendar.date <  '{$criteria->getEndDate()}'
  GROUP BY calendar.year,calendar.month
) AS time_series

LEFT JOIN {$criteria->getTableName()} ON
 (date({$criteria->getTableName()}.{$criteria->getRangeStart()})
    BETWEEN time_series.date AND (time_series.date + INTERVAL 1 MONTH))
 OR
 (date({$criteria->getTableName()}.{$criteria->getRangeEnd()})
    BETWEEN time_series.date AND (time_series.date + INTERVAL 1 MONTH))
 OR
 (date({$criteria->getTableName()}.{$criteria->getRangeStart()}) <= time_series.date AND
    date({$criteria->getTableName()}.{$criteria->getRangeEnd()}) >= (time_series.date + INTERVAL 1 MONTH))

{$criteria->getJoin()}

{$where}

GROUP BY time_series.time_key
");

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            if (isset($row->name)) {
                $dbData[$row->name]["{$row->year}-{$row->month}"] = $value;
            } else {
                $dbData[' ']["{$row->year}-{$row->month}"] = $value;
            }
        }
        return $dbData;
    }

    public function getDatabaseData(Criteria $criteria, $tag)
    {
        $dbData = [];

        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
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
GROUP BY calendar.year,calendar.month
		");

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            $dbData["{$row->year}-{$row->month}"] = $value;
        }
        return $dbData;
    }

    public function getDatabaseSeriesData(Criteria $criteria)
    {
        $dbData = [];

        $res = $this->database->query("SELECT {$criteria->getValueField()} AS value,
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
GROUP BY calendar.year,calendar.month" . $this->getGroupBy($criteria->getGroupBy()) . '
		');

        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            if (isset($row->name)) {
                $dbData[$row->name]["{$row->year}-{$row->month}"] = $value;
            } else {
                $dbData[' ']["{$row->year}-{$row->month}"] = $value;
            }
        }
        return $dbData;
    }
}
