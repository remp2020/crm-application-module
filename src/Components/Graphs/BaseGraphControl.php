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
            $xDataString = implode(',', $yDataArray);
            $graphDataJs .= strpos($xData, 'new Date') !== false
                ? "[$xData, $xDataString],"
                : "['$xData', $xDataString],";
        }

        return trim($graphDataJs, ',');
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
