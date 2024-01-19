<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Repository;
use Crm\ApplicationModule\Selection;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class MeasurementGroupsRepository extends Repository
{
    protected $tableName = 'measurement_groups';

    final public function add(
        string $measurementId,
        string $title
    ): ActiveRow {
        $id = $this->insert([
            'title' => $title,
            'measurement_id' => $measurementId,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
        return $this->find($id);
    }

    final public function findByMeasurementId(string $measurementId): Selection
    {
        return $this->getTable()->where([
            'measurement_id' => $measurementId,
        ]);
    }

    final public function findGroup(string $measurementId, string $title): ?ActiveRow
    {
        return $this->getTable()->where([
            'title' => $title,
            'measurement_id' => $measurementId,
        ])->fetch();
    }

    final public function addOrFind(string $measurementId, string $title): ActiveRow
    {
        $grouping = $this->findGroup($measurementId, $title);
        if ($grouping === null) {
            $grouping = $this->add($measurementId, $title);
        }
        return $grouping;
    }
}
