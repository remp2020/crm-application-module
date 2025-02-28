<?php

namespace Crm\ApplicationModule\Models\Database;

trait DateFieldsProcessorTrait
{
    public function processDateFields($fields)
    {
        foreach ($fields as $i => $field) {
            if ($field instanceof \DateTime || $field instanceof \DateTimeImmutable) {
                $fields[$i] = $field->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            }
        }

        return $fields;
    }
}
