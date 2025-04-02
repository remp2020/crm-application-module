<?php

namespace Crm\ApplicationModule\Models\Graphs\Scale;

use Crm\ApplicationModule\Models\Graphs\Criteria;

interface ScaleInterface
{
    public function getKeys(string $start, string $end);

    public function getDatabaseRangeData(Criteria $criteria);

    public function getDatabaseSeriesData(Criteria $criteria);
}
