<?php

namespace Crm\ApplicationModule\Models\Measurements;

use Crm\ApplicationModule\Models\Measurements\Aggregation\Aggregation;
use DateTime;

class Point
{
    private Aggregation $aggregation;

    private float $value;

    private DateTime $date;

    private ?string $groupingKey;

    public function __construct(
        Aggregation $aggregation,
        float $value,
        DateTime $date,
        ?string $groupingKey = null,
    ) {
        $this->aggregation = $aggregation;
        $this->value = $value;
        $this->date = $date;
        $this->groupingKey = $groupingKey;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function date(): DateTime
    {
        return $this->date;
    }

    public function key(): string
    {
        return $this->aggregation->key($this->date);
    }

    public function aggregation(): Aggregation
    {
        return $this->aggregation;
    }

    public function groupKey(): ?string
    {
        return $this->groupingKey;
    }
}
