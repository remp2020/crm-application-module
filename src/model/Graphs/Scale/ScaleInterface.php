<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use Crm\ApplicationModule\Graphs\Criteria;

interface ScaleInterface
{
    public function getKeys($start, $end);

    public function getDatabaseData(Criteria $criteria, $tag);

    public function getDatabaseRangeData(Criteria $criteria);

    public function getDatabaseSeriesData(Criteria $criteria);
}
