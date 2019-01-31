<?php

namespace Crm\ApplicationModule\Graphs\Scale;

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
        return ($series != '') ? true : false;
    }

    protected function formatData($res)
    {
        $dbData = [];
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
