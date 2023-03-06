<?php

namespace Crm\ApplicationModule\Models\Measurements\Aggregation;

use DateInterval;
use DateTime;

class Day extends Aggregation
{
    public function select(string $dateField): array
    {
        return [
            "EXTRACT(YEAR FROM {$dateField}) AS year",
            "EXTRACT(MONTH FROM {$dateField}) AS month",
            "EXTRACT(DAY FROM {$dateField}) AS day"
        ];
    }

    public function nextDate(DateTime $date): DateTime
    {
        $beginningOfDay = (clone $date)->modify('midnight');
        if ($beginningOfDay <= $date) {
            return $beginningOfDay->add(new DateInterval('P1D'));
        }
        return $beginningOfDay;
    }

    public function key(DateTime $date): string
    {
        return $date->format("Y-m-d");
    }

    public function store(DateTime $date): DateData
    {
        return new DateData(
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            null
        );
    }

    public function unStore(DateData $data): DateTime
    {
        return \Nette\Utils\DateTime::fromParts($data->year(), $data->month(), $data->day());
    }
}
