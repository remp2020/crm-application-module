<?php

namespace Crm\ApplicationModule\Models\Measurements\Aggregation;

use DateInterval;
use DateTime;

class Year extends Aggregation
{
    public function select(string $dateField): array
    {
        return [
            "EXTRACT(YEAR FROM {$dateField}) AS year",
        ];
    }

    public function nextDate(DateTime $date): DateTime
    {
        $beginningOfYear = new DateTime($date->format('Y') . '-01-01');
        if ($beginningOfYear <= $date) {
            return $beginningOfYear->add(new DateInterval('P1Y'));
        }
        return $beginningOfYear;
    }

    public function key(DateTime $date): string
    {
        return $date->format("Y");
    }

    public function store(DateTime $date): DateData
    {
        return new DateData(
            $date->format('Y'),
            null,
            null,
            null,
        );
    }

    public function unStore(DateData $data): DateTime
    {
        return \Nette\Utils\DateTime::fromParts($data->year(), 1, 1);
    }
}
