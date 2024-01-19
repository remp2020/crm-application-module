<?php

namespace Crm\ApplicationModule\Models\Graphs;

class Criteria
{
    private $start = 'today 00:00';

    private $end = 'today 23:59';

    private $timeField = 'created_at';

    public $rangeFields = [];

    private $tableName = 'undefied';

    private $valueField = 'COUNT(*)';

    private $where = '';

    private $join = '';

    private $series = '';

    private $group = '';

    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    public function getSeries()
    {
        return $this->series;
    }

    public function setGroupBy($group)
    {
        $this->group = $group;
        return $this;
    }

    public function getGroupBy()
    {
        return $this->group;
    }

    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getStartDate($dateFormat = 'Y-m-d')
    {
        return date($dateFormat, strtotime($this->start));
    }

    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getEndDate($dateFormat = 'Y-m-d')
    {
        return date($dateFormat, strtotime($this->end));
    }

    public function setTimeField($timeField)
    {
        $this->timeField = $timeField;
        return $this;
    }

    public function getTimeField()
    {
        return $this->timeField;
    }

    public function setRangeFields($start, $end)
    {
        $this->rangeFields = [$start, $end];
        return $this;
    }

    public function getRangeStart()
    {
        return $this->rangeFields[0];
    }

    public function getRangeEnd()
    {
        return $this->rangeFields[1];
    }

    public function getRangeField()
    {
        return [$this->getRangeStart(), $this->getRangeEnd()];
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setValueField($valueField)
    {
        $this->valueField = $valueField;
        return $this;
    }

    public function getValueField()
    {
        return $this->valueField;
    }

    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function setJoin($join)
    {
        $this->join = $join;
        return $this;
    }

    public function getJoin()
    {
        return $this->join;
    }
}
