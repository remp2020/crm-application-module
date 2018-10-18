<?php

namespace Crm\ApplicationModule\Graphs;

use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;
use Nette\Database\Context;

class GraphData
{
    const SCALE_DAYS = 'days';
    const SCALE_WEEKS = 'weeks';
    const SCALE_MONTHS = 'months';

    /** @var Context */
    protected $database;

    /** @var ScaleInterface */
    private $scale;

    private $scaleRange;

    private $scaleFactory;

    private $graphDataItems = [];

    private $start;
    private $end = null;

    public function __construct(Context $database, ScaleFactory $scaleFactory)
    {
        $this->database = $database;
        $this->scaleFactory = $scaleFactory;
    }

    public function clear()
    {
        $this->graphDataItems = [];
    }

    public function addGraphDataItem($graphDataItem)
    {
        $this->graphDataItems[] = $graphDataItem;
    }

    public function setScale(ScaleInterface $scale)
    {
        $this->scale = $scale;
        foreach ($this->graphDataItems as $graphDataItem) {
            $graphDataItem->setScale($this->scale);
        }
        return $this;
    }

    public function setScaleRange($range)
    {
        $this->scaleRange = $range;
        return $this;
    }

    public function setStart($start)
    {
        $this->start = $start;
        foreach ($this->graphDataItems as $graphDataItem) {
            $graphDataItem->getCriteria()->setStart($this->start);
        }
        return $this;
    }

    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    public function getData()
    {
        return $this->getSeriesData();
    }

    public function getSeriesData()
    {
        $series = [];

        /** @var GraphDataItem $graphDataItem */
        foreach ($this->graphDataItems as $graphDataItem) {
            $scale = $this->scaleFactory->create($graphDataItem->getScaleProvider(), $this->scaleRange);
            $graphDataItem->setScale($scale);

            $data = $graphDataItem->getSeriesData();

            foreach ($data as $k => $v) {
                $series[$k] = $v;
            }
        }

        $dateKeys = [];
        $serieKeys = array_keys($series);

        // get keys of the series (all series have the same key, we just need the first)
        // we want to trim initial zeros here
        foreach ($series as $k => $serie) {
            $dateKeys = array_keys($serie);
            break;
        }
        foreach ($dateKeys as $dateKey) {
            $omit = true;
            foreach ($serieKeys as $serieKey) {
                // if at least one serie has non-zero value set, stop trimming of series
                if (isset($series[$serieKey][$dateKey]) && $series[$serieKey][$dateKey] > 0) {
                    $omit = false;
                    break;
                }
            }
            if ($omit) {
                foreach ($serieKeys as $serieKey) {
                    unset($series[$serieKey][$dateKey]);
                }
            } else {
                // no point to check further, we got to the point where at least one serie has non-zero value
                // we want to display the rest
                break;
            }
        }

        return $series;
    }
}
