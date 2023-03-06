<?php

namespace Crm\ApplicationModule\Models\Graphs\Scale\Measurements;

use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;
use Crm\ApplicationModule\Models\Measurements\Aggregation\Month;
use Crm\ApplicationModule\Models\Measurements\Repository\MeasurementGroupValuesRepository;
use Crm\ApplicationModule\Models\Measurements\Repository\MeasurementValuesRepository;
use Nette\Utils\DateTime;

class MonthScale implements ScaleInterface
{
    private MeasurementValuesRepository $measurementValuesRepository;
    private MeasurementGroupValuesRepository $measurementGroupValuesRepository;

    public function __construct(
        MeasurementValuesRepository $measurementValuesRepository,
        MeasurementGroupValuesRepository $measurementGroupValuesRepository
    ) {
        $this->measurementValuesRepository = $measurementValuesRepository;
        $this->measurementGroupValuesRepository = $measurementGroupValuesRepository;
    }

    public function getKeys(string $start, string $end)
    {
        $aggregation = new Month();
        $startDate = DateTime::from($start);
        $endDate = DateTime::from($end);

        $diff = $startDate->diff($endDate);
        $months = ((int) $diff->format('%y') * 12) + (int) $diff->format('%m') + 1;
        $result = [];

        $result[$aggregation->key($startDate)] = $startDate->format('Y-m');
        for ($i = 0; $i < $months; $i++) {
            $startDate = $aggregation->nextDate($startDate);
            $result[$aggregation->key($startDate)] = $aggregation->key($startDate);
        }

        return $result;
    }

    public function getDatabaseData(Criteria $criteria, string $tag)
    {
        // TODO: Implement getDatabaseData() method.
    }

    public function getDatabaseRangeData(Criteria $criteria)
    {
        // TODO: Implement getDatabaseRangeData() method.
    }

    public function getDatabaseSeriesData(Criteria $criteria)
    {
        if ($group = $criteria->getGroupBy()) {
            $measurementValues = $this->measurementGroupValuesRepository
                ->values(
                    $criteria->getSeries(),
                    $group,
                    DateTime::from($criteria->getStartDate('Y-m-d 00:00:00')),
                    DateTime::from($criteria->getEndDate('Y-m-d 23:59:59')),
                )
                ->select('measurement_group_values.key, SUM(measurement_group_values.value) AS sum, measurement_value.sorting_day')
                ->where('day IS NULL')
                ->where('week IS NULL')
                ->where('month IS NOT NULL')
                ->group('key, measurement_value.sorting_day');
        } else {
            $measurementValues = $this->measurementValuesRepository
                ->values(
                    $criteria->getSeries(),
                    DateTime::from($criteria->getStartDate('Y-m-d 00:00:00')),
                    DateTime::from($criteria->getEndDate('Y-m-d 23:59:59')),
                )
                ->where('day IS NULL')
                ->where('week IS NULL')
                ->where('month IS NOT NULL');
        }

        $aggregation = new Month();

        $result = [];
        foreach ($measurementValues as $measurementValue) {
            if (isset($measurementValue->key)) {
                $result[$measurementValue->key][$aggregation->key($measurementValue->sorting_day)] = $measurementValue->sum;
            } else {
                $result[' '][$aggregation->key($measurementValue->sorting_day)] = $measurementValue->value;
            }
        }

        return $result;
    }
}
