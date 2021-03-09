<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use DateTime;

abstract class DayScale extends ScaleBase implements ScaleInterface
{
    public function getKeys(string $start, string $end)
    {
        $actual = new DateTime(date('Y-m-d', strtotime($start)));
        $endDateTime = new DateTime(date('Y-m-d', strtotime($end)));

        $diff = $actual->diff($endDateTime);
        $days = (int) $diff->format('%a');
        $result = [];

        $result[$actual->format('Y-n-j')] = "new Date({$actual->format('Y,n-1,j')})";
        for ($i = 0; $i < $days; $i++) {
            $actual = $actual->modify('+1 days');
            $result[$actual->format('Y-n-j')] = "new Date({$actual->format('Y,n-1,j')})";
        }

        return $result;
    }
}
