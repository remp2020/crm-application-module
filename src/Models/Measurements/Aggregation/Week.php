<?php

namespace Crm\ApplicationModule\Models\Measurements\Aggregation;

use DateInterval;
use DateTime;

class Week extends Aggregation
{
    public function select(string $dateField): array
    {
        return [
            "DATE_FORMAT({$dateField}, '%x') AS year", // Year for the week where Monday is the first day of the week.
            "WEEK({$dateField}, 3) AS week", // Mode 3 - Week 1 is the first week with 4 or more days this year
        ];
    }

    public function nextDate(DateTime $date): DateTime
    {
        $beginningOfWeek = $date->modifyClone('this week')->setTime(0, 0);
        if ($beginningOfWeek <= $date) {
            return $beginningOfWeek->add(new DateInterval('P7D'));
        }
        return $beginningOfWeek;
    }

    public function key(DateTime $date): string
    {
        return $date->format("o-W");
    }

    public function store(DateTime $date): DateData
    {
        return new DateData(
            $date->format('o'),
            null,
            null,
            $date->format('W')
        );
    }

    public function unStore(DateData $data): DateTime
    {
        return (new DateTime())->setISODate($data->year(), $data->week())->setTime(0, 0);
    }
}
