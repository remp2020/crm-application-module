<?php

namespace Crm\ApplicationModule\Models\Graphs\Scale\Measurements;

use Crm\ApplicationModule\Models\Graphs\ScaleFactory;

class RangeScaleFactory
{
    public const PROVIDER_MEASUREMENT = 'measurement';

    private DayScale $dayScale;
    private WeekScale $weekScale;
    private MonthScale $monthScale;
    private YearScale $yearScale;

    public function __construct(
        DayScale $dayScale,
        WeekScale $weekScale,
        MonthScale $monthScale,
        YearScale $yearScale
    ) {
        $this->dayScale = $dayScale;
        $this->weekScale = $weekScale;
        $this->monthScale = $monthScale;
        $this->yearScale = $yearScale;
    }

    public function create($group)
    {
        switch ($group) {
            case ScaleFactory::RANGE_DAY:
                return clone $this->dayScale;
            case ScaleFactory::RANGE_WEEK:
                return clone $this->weekScale;
            case ScaleFactory::RANGE_MONTH:
                return clone $this->monthScale;
            case ScaleFactory::RANGE_YEAR:
                return clone $this->yearScale;
        }
        throw new \Exception("unhandled group scale [{$group}]");
    }
}
