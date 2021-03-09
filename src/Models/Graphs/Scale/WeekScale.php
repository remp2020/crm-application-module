<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use DateTime;

abstract class WeekScale extends ScaleBase implements ScaleInterface
{
    public function getKeys(string $start, string $end)
    {
        $actual = new DateTime(date('Y-m-d', strtotime($start)));
        $endDateTime = new DateTime(date('Y-m-d', strtotime($end)));
        $diff = $actual->diff($endDateTime);

        $days = (int)$diff->format('%a');
        $weeks = ceil($days / 7);
        $result = [];
        $actual = $actual->setISODate($actual->format('o'), $actual->format('W'));
        $result[$actual->format('Y-m-d')] = "new Date({$actual->format('Y,n-1,j')})";
        for ($i = 0; $i < $weeks; $i++) {
            $actual = $actual->modify('+1 week');
            $result[$actual->format('Y-m-d')] = "new Date({$actual->format('Y,n-1,j')})";
        }

        return $result;
    }
}
