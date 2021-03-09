<?php

namespace Crm\ApplicationModule\Models\Measurements\Repository;

use Crm\ApplicationModule\Models\Measurements\PointAggregate;
use Crm\ApplicationModule\Repository;
use Nette\Caching\Storage;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

class MeasurementGroupValuesRepository extends Repository
{
    protected static $groupCache;

    protected $tableName = 'measurement_group_values';

    protected MeasurementGroupsRepository $measurementGroupsRepository;

    public function __construct(
        Explorer $database,
        MeasurementGroupsRepository $measurementGroupsRepository,
        Storage $cacheStorage = null
    ) {
        parent::__construct($database, $cacheStorage);
        $this->measurementGroupsRepository = $measurementGroupsRepository;
    }

    final public function add(ActiveRow $measurementValue, PointAggregate $pointAggregate)
    {
        $groups = $pointAggregate->getGroups();
        if (!count($groups)) {
            return;
        }

        $insertData = [];
        foreach ($groups as $group) {
            foreach ($pointAggregate->groupPoints($group) as $point) {
                $insertData[] = [
                    'measurement_group_id' => $this->getMeasurementGroupId($measurementValue->measurement_id, $group),
                    'measurement_value_id' => $measurementValue->id,
                    'key' => $point->groupKey(),
                    'value' => $point->value(),
                ];
            }
        }

        $this->database->query("INSERT INTO {$this->tableName}", $insertData);
    }

    public function values(string $measurementCode, string $group, DateTime $from, DateTime $to): Selection
    {
        return $this->getTable()
            ->where('measurement_group.measurement.code = ?', $measurementCode)
            ->where('measurement_group.title = ?', $group)
            ->where('sorting_day >= ?', $from)
            ->where('sorting_day <= ?', $to)
            ->order('sorting_day');
    }

    protected function getMeasurementGroupId(string $measurementId, string $group): int
    {
        if (!isset(self::$groupCache[$group])) {
            self::$groupCache[$group] = $this->measurementGroupsRepository->addOrFind($measurementId, $group)->id;
        }
        return self::$groupCache[$group];
    }
}
