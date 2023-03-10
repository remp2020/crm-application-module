<?php

namespace Crm\ApplicationModule\Components\Graphs;

use Nette\Application\UI\Control;
use Nette\Utils\Random;

abstract class BaseGraphControl extends Control
{
    protected $series = [];

    protected function generateGraphId()
    {
        return Random::generate();
    }

    protected function getGroupedData()
    {
        foreach ($this->series as $serie) {
            foreach ($serie as $xData => $yData) {
                $groupedData[$xData][] = $yData;
            }
        }
        return $groupedData ?? [];
    }

    protected function getDataForJs()
    {
        $graphDataJs = '';
        foreach ($this->getGroupedData() as $xData => $yDataArray) {
            $yDataString = implode(',', $yDataArray);
            $graphDataJs .= strpos($xData, 'new Date') !== false
                ? "[$xData, $yDataString],"
                : "['$xData', $yDataString],";
        }

        return trim($graphDataJs, ',');
    }

    protected function getChartViewWindowMin(): ?int
    {
        $globalMax = PHP_INT_MIN;
        $globalMin = PHP_INT_MAX;
        foreach ($this->getGroupedData() as $xData => $yDataArray) {
            if (($localMin = min($yDataArray)) < $globalMin) {
                $globalMin = $localMin;
            }
            if (($localMax = max($yDataArray)) > $globalMax) {
                $globalMax = $localMax;
            }
        }

        // If the difference between chart extremes is greater than 30%, the chart should be readable without
        // any further manipulation.
        if ($globalMin < $globalMax * 0.7) {
            return null;
        }

        $viewWindowMin = floor($globalMin * 0.95);
        return max($viewWindowMin, 0);
    }

    protected function isFirstColumnString(): bool
    {
        foreach ($this->getGroupedData() as $xData => $yDataArray) {
            // first column can be either date or string
            return strpos($xData, 'new Date') === false;
        }
        return false;
    }
}
