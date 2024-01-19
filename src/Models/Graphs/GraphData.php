<?php

namespace Crm\ApplicationModule\Models\Graphs;

use Crm\ApplicationModule\Models\Graphs\Scale\ScaleInterface;
use Nette\Database\Explorer;

class GraphData
{
    const SCALE_DAYS = 'days';
    const SCALE_WEEKS = 'weeks';
    const SCALE_MONTHS = 'months';

    protected Explorer $database;

    private ScaleInterface $scale;

    private $scaleRange;

    private ScaleFactory $scaleFactory;

    /** @var GraphDataItem[]  */
    private array $graphDataItems = [];

    private $start;
    private $end = null;

    public function __construct(Explorer $database, ScaleFactory $scaleFactory)
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
        foreach ($this->graphDataItems as $graphDataItem) {
            $graphDataItem->getCriteria()->setEnd($this->end);
        }
        return $this;
    }

    public function getData()
    {
        return $this->getSeriesData();
    }

    public function getSeriesData()
    {
        $series = [];

        // Some series might not have complete data. Measurement-based calculations might not be ready for "today" yet,
        // and we need to align all of the series to use the exact same set of dates/keys.
        // If there are series with extra dates (due to the combination of MySQL and Measurement providers), we need
        // to throw away these extra dates.
        $seriesLength = PHP_INT_MAX;

        /** @var GraphDataItem $graphDataItem */
        foreach ($this->graphDataItems as $graphDataItem) {
            $scale = $this->scaleFactory->create($graphDataItem->getScaleProvider(), $this->scaleRange);
            $graphDataItem->setScale($scale);

            $data = $graphDataItem->getSeriesData();

            foreach ($data as $serie => $values) {
                $series[$serie] = $values;
                $seriesLength = min($seriesLength, count($values));
            }
        }

        $dateKeys = [];
        $serieKeys = array_keys($series);

        // get keys of the series (all series have the same keys, we just need the first)
        // we want to trim initial zeros here
        foreach ($series as $k => $_) {
            $series[$k] = array_slice($series[$k], 0, $seriesLength, true);
            $dateKeys = array_keys($series[$k]);
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
