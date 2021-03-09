<?php

namespace Crm\ApplicationModule\Models\Measurements;

use Nette\Database\Explorer;

class MeasurementManager
{
    private array $measurements = [];

    private Explorer $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function register(BaseMeasurement $measurement): void
    {
        $measurement->setDatabase($this->db);
        $this->measurements[$measurement->code()] = $measurement;
    }

    public function getById(string $id): BaseMeasurement
    {
        return $this->measurements[$id];
    }

    /**
     * @return BaseMeasurement[]
     */
    public function getMeasurements(): array
    {
        return $this->measurements;
    }
}
