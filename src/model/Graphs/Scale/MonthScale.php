<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use DateTime;

abstract class MonthScale extends ScaleBase implements ScaleInterface
{
    public function getKeys($start, $end)
    {
        $actual = new DateTime(date('Y-m-d', strtotime($start)));
        $endDateTime = new DateTime(date('Y-m-d', strtotime($end)));
        $diff = $actual->diff($endDateTime);

        $months = intval($diff->m + $diff->y * 12);
        $result = [];

        $result[$actual->format('Y-n')] = "new Date({$actual->format('Y,n-1')})";
        for ($i = 0; $i < $months; $i++) {
            $actual = $actual->modify('+1 month');
            $result[$actual->format('Y-n')] = "new Date({$actual->format('Y,n-1')})";
        }

        return $result;
    }
}
