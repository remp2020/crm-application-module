<?php

namespace Crm\ApplicationModule\Graphs;

use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;

class GraphDataItem
{
    const SCALE_DAYS = 'days';
    const SCALE_WEEKS = 'weeks';
    const SCALE_MONTHS = 'months';

    /** @var ScaleInterface */
    private $scale;

    private $scaleProvider = 'mysql';

    /** @var Criteria */
    private $criteria;

    public $name = '';

    public $tag = '';

    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria)
    {
        $this->criteria = $criteria;
        return $this;
    }

    public function setScaleProvider($provider)
    {
        $this->scaleProvider = $provider;
    }

    public function getScaleProvider()
    {
        return $this->scaleProvider;
    }

    public function setScale(ScaleInterface $scale)
    {
        $this->scale = $scale;
        return $this;
    }

    public function getData()
    {
        $zeroKeys = $this->scale->getKeys($this->criteria->getStart(), $this->criteria->getEnd());

        $dbData = $this->scale->getDatabaseData($this->criteria, $this->tag);

        return $this->formatData($zeroKeys, $dbData);
    }

    public function getSeriesData()
    {
        $zeroKeys = $this->scale->getKeys($this->criteria->getStart(), $this->criteria->getEnd());

        if (count($this->criteria->rangeFields) > 0) {
            $dbData = $this->scale->getDatabaseRangeData($this->criteria);
        } else {
            $dbData = $this->scale->getDatabaseSeriesData($this->criteria);
        }

        $db = [];
        if (empty($dbData)) {
            $db[$this->name] = $this->formatData($zeroKeys, []);
        }
        foreach ($dbData as $k => $v) {
            $db[$this->name . $k] = $this->formatData($zeroKeys, $v);
        }
        return $db;
    }

    public function getRangeData()
    {
        $zeroKeys = $this->scale->getKeys($this->criteria->getStart(), $this->criteria->getEnd());

        $dbData = $this->scale->getDatabaseRangeData($this->criteria);

        return $dbData;
    }

    private function formatData($zeroValues, $dbData)
    {
        $result = [];
        foreach ($zeroValues as $key => $date) {
            if (isset($dbData[$key])) {
                $result[$date] = $dbData[$key];
            } else {
                $result[$date] = 0;
            }
        }
        return $result;
    }
}
