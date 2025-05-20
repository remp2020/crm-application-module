<?php

namespace Crm\ApplicationModule\Models\Measurements\Aggregation;

use DateInterval;
use DateTime;

class Month extends Aggregation
{
    public function select(string $dateField): array
    {
        return [
            "EXTRACT(YEAR FROM {$dateField}) AS year",
            "EXTRACT(MONTH FROM {$dateField}) AS month",
        ];
    }

    public function nextDate(DateTime $date): DateTime
    {
        $beginningOfMonth = (clone $date)->modify('first day of this month')->setTime(0, 0);
        if ($beginningOfMonth <= $date) {
            return $beginningOfMonth->add(new DateInterval('P1M'));
        }
        return $beginningOfMonth;
    }

    public function key(DateTime $date): string
    {
        return $date->format("Y-m");
    }

    public function store(DateTime $date): DateData
    {
        return new DateData(
            $date->format('Y'),
            $date->format('m'),
            null,
            null,
        );
    }

    public function unStore(DateData $data): DateTime
    {
        return \Nette\Utils\DateTime::fromParts($data->year(), $data->month(), 1);
    }
}
