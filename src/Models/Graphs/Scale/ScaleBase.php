<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use Nette\Database\ResultSet;

abstract class ScaleBase
{
    protected function getGroupBy($groupBy)
    {
        if ($groupBy != '') {
            return ', ' . $groupBy;
        } else {
            return '';
        }
    }

    protected function getSeries($series)
    {
        if ($series != '') {
            return "{$series} AS \"name\",";
        } else {
            return '';
        }
    }

    protected function isSeries($series)
    {
        return $series != '';
    }

    protected function formatData(ResultSet $res)
    {
        $dbData = [];
        /** @var \Nette\Database\Table\IRow $row */
        foreach ($res as $row) {
            $value = 0;
            if ($row->id != null) {
                $value = $row['value'];
            }
            if (isset($row->name)) {
                $dbData[$row->name]["{$row->year}-{$row->month}-{$row->day}"] = $value;
            }
            $dbData[$row->name]["{$row->year}-{$row->month}-{$row->day}"] = $value;
        }
        return $dbData;
    }
}
