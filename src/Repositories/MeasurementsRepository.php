<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Models\Measurements\BaseMeasurement;
use Crm\ApplicationModule\Repository;
use Crm\ApplicationModule\Selection;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class MeasurementsRepository extends Repository
{
    protected $tableName = 'measurements';

    final public function add(
        string $code,
        string $title,
        string $description
    ): ActiveRow {
        return $this->insert([
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
    }

    public function findByCode(string $code): ?ActiveRow
    {
        return $this->getTable()->where(['code' => $code])->fetch();
    }

    public function all(): Selection
    {
        return $this->getTable();
    }

    public function exists(BaseMeasurement $measurement): bool
    {
        return $this->getTable()->where(['code' => $measurement->code()])->count('*') > 0;
    }
}
