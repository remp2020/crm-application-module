<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Models\Database\ActiveRow;
use Crm\ApplicationModule\Models\Database\Repository;
use Crm\ApplicationModule\Models\Measurements\PointAggregate;
use DateTime;
use Nette\Caching\Storage;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class MeasurementValuesRepository extends Repository
{
    protected $tableName = 'measurement_values';

    private MeasurementGroupValuesRepository $measurementGroupValuesRepository;

    public function __construct(
        Explorer $database,
        MeasurementGroupValuesRepository $measurementGroupValuesRepository,
        Storage $cacheStorage = null,
    ) {
        parent::__construct($database, $cacheStorage);
        $this->measurementGroupValuesRepository = $measurementGroupValuesRepository;
    }

    public function add(ActiveRow $measurement, PointAggregate $pointAggregate, DateTime $epoch)
    {
        $point = $pointAggregate->point();

        $data = $point->aggregation()->store($point->date())->getArray();
        $data['measurement_id'] = $measurement->id;
        $data['value'] = $point->value();

        // For incomplete periods use $epoch as a sorting day. This is to:
        // - Delete correct values via $from later in the process
        // - Have an actual information about when does the period start
        if ($data['sorting_day'] < $epoch) {
            $data['sorting_day'] = $epoch;
        }

        $row = $this->getTable()->insert($data);
        $this->measurementGroupValuesRepository->add($row, $pointAggregate);
        return $row;
    }

    public function deleteValues(ActiveRow $measurementRow, DateTime $from, DateTime $to): bool
    {
        return (bool) $this->getTable()
            ->where('measurement_id = ?', $measurementRow->id)
            ->where('sorting_day >= ?', (clone $from)->setTime(0, 0))
            ->where('sorting_day <= ?', $to)
            ->delete();
    }

    public function values(string $measurementCode, DateTime $from, DateTime $to): Selection
    {
        return $this->getTable()
            ->where('measurement.code = ?', $measurementCode)
            ->where('sorting_day >= ?', (clone $from)->setTime(0, 0))
            ->where('sorting_day <= ?', $to)
            ->order('sorting_day');
    }
}
