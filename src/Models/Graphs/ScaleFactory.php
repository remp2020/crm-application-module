<?php

namespace Crm\ApplicationModule\Models\Graphs;

use Crm\ApplicationModule\Models\Graphs\Scale\Measurements\RangeScaleFactory as MeasurementsScaleFactory;
use Crm\ApplicationModule\Models\Graphs\Scale\ScaleInterface;

class ScaleFactory
{
    public const RANGE_DAY = 'day';
    public const RANGE_WEEK = 'week';
    public const RANGE_MONTH = 'month';
    public const RANGE_YEAR = 'year';

    private \Crm\ApplicationModule\Models\Graphs\Scale\Mysql\RangeScaleFactory $mysqlScaleFactory;
    private MeasurementsScaleFactory $measurementsScaleFactory;

    public function __construct(
        \Crm\ApplicationModule\Models\Graphs\Scale\Mysql\RangeScaleFactory $mysqlScaleFactory,
        MeasurementsScaleFactory $measurementsScaleFactory,
    ) {
        $this->mysqlScaleFactory = $mysqlScaleFactory;
        $this->measurementsScaleFactory = $measurementsScaleFactory;
    }

    public function create($provider, $range): ScaleInterface
    {
        switch ($provider) {
            case \Crm\ApplicationModule\Models\Graphs\Scale\Mysql\RangeScaleFactory::PROVIDER_MYSQL:
                $factory = $this->mysqlScaleFactory;
                break;
            case MeasurementsScaleFactory::PROVIDER_MEASUREMENT:
                $factory = $this->measurementsScaleFactory;
                break;
            default:
                throw new \Exception("unhandled scale provider [{$provider}]");
        }
        return $factory->create($range);
    }
}
