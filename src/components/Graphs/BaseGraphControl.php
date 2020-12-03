<?php

namespace Crm\ApplicationModule\Components\Graphs;

use Nette\Application\UI\Control;

abstract class BaseGraphControl extends Control
{
    protected $series = [];

    protected function generateGraphId()
    {
        return md5(rand(0, 1000) . microtime() . rand(0, 1000));
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
            $xDataString = implode(',', $yDataArray);
            $graphDataJs .= strpos($xData, 'new Date') !== false
                ? "[$xData, $xDataString],"
                : "['$xData', $xDataString],";
        }

        return trim($graphDataJs, ',');
    }
}
