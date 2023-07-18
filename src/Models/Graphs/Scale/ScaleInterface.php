<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use Crm\ApplicationModule\Graphs\Criteria;

interface ScaleInterface
{
    public function getKeys(string $start, string $end);

    /**
     * @deprecated Not used by code use {@see ScaleInterface::getDatabaseRangeData()} instead.
     */
    public function getDatabaseData(Criteria $criteria, string $tag);

    public function getDatabaseRangeData(Criteria $criteria);

    public function getDatabaseSeriesData(Criteria $criteria);
}
