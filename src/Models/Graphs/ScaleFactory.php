<?php

namespace Crm\ApplicationModule\Graphs;

use Crm\ApplicationModule\Graphs\Scale\Mysql\RangeScaleFactory as MysqlScaleFactory;
use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;
use Crm\ApplicationModule\Models\Graphs\Scale\Measurements\RangeScaleFactory as MeasurementsScaleFactory;

class ScaleFactory
{
    public const RANGE_DAY = 'day';
    public const RANGE_WEEK = 'week';
    public const RANGE_MONTH = 'month';
    public const RANGE_YEAR = 'year';

    private MysqlScaleFactory $mysqlScaleFactory;
    private MeasurementsScaleFactory $measurementsScaleFactory;

    public function __construct(
        MysqlScaleFactory $mysqlScaleFactory,
        MeasurementsScaleFactory $measurementsScaleFactory
    ) {
        $this->mysqlScaleFactory = $mysqlScaleFactory;
        $this->measurementsScaleFactory = $measurementsScaleFactory;
    }

    public function create($provider, $range): ScaleInterface
    {
        switch ($provider) {
            case MysqlScaleFactory::PROVIDER_MYSQL:
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
