<?php

namespace Crm\ApplicationModule\Models\Measurements\Aggregation;

use DateTime;

abstract class Aggregation
{
    /**
     * select returns fields for database select containing date extraction function based on the provided $dateField.
     */
    abstract public function select(string $dateField): array;

    /**
     * nextDate returns next available date for the aggregation that's coming after the provided $date.
     */
    abstract public function nextDate(DateTime $date): DateTime;

    /**
     * key returns identifier of the provided $date within the aggregation.
     * For example "Year" aggregation, all $dates of current year would return the same key()
     */
    abstract public function key(DateTime $date): string;

    /**
     * store stores (expands) the $date data into the DateData data fields.
     */
    abstract public function store(DateTime $date): DateData;

    /**
     * unStore builds (compacts) the DateTime based on the provided DateData object.
     */
    abstract public function unStore(DateData $date): DateTime;

    public function group(array $select): string
    {
        $items = [];
        for ($i = 1; $i <= count($select); $i++) {
            $items[] = $i;
        }
        return implode(",", $items);
    }
}
