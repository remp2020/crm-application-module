<?php

namespace Crm\ApplicationModule\Models\Measurements;

use Nette\Database\Explorer;

abstract class BaseMeasurement
{
    /**
     * GROUPINGS in the child classes configures separate groups the measurement should be calculated for.
     * Value "null" indicates no grouping which is the default for all measurements.
     * If you want the measurement to be calculated based on a more specific split, measurement should specify it here.
     */
    protected const GROUPS = [];

    /**
     * CODE identifies the measurement implementation. Measurement's code needs to be referenced when you need to fetch
     * the generated data series.
     */
    public const CODE = 'unknown';

    protected $db;

    public function code(): string
    {
        return static::CODE;
    }

    public function groups(): array
    {
        return array_merge([null], static::GROUPS);
    }

    public function setDatabase(Explorer $db): void
    {
        $this->db = $db;
    }

    protected function db(): Explorer
    {
        return $this->db;
    }
}
