<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use Crm\ApplicationModule\Graphs\Criteria;

interface ScaleInterface
{
    public function getKeys(string $start, string $end);

    public function getDatabaseData(Criteria $criteria, string $tag);

    public function getDatabaseRangeData(Criteria $criteria);

    public function getDatabaseSeriesData(Criteria $criteria);
}
