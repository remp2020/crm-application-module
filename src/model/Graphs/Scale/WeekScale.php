<?php

namespace Crm\ApplicationModule\Graphs\Scale;

use DateTime;

abstract class WeekScale extends ScaleBase implements ScaleInterface
{
    public function getKeys($start, $end)
    {
        $actual = new DateTime(date('Y-m-d', strtotime($start)));
        $endDateTime = new DateTime(date('Y-m-d', strtotime($end)));
        $diff = $actual->diff($endDateTime);

        $days = intval($diff->format('%a'));
        $weeks = ceil($days / 7);
        $result = [];
        $actual->setISODate($actual->format('Y'), $actual->format('W'));
        $result[$actual->format('Y-m-d')] = "new Date({$actual->format('Y,n-1,j')})";
        for ($i = 0; $i < $weeks; $i++) {
            $actual = $actual->modify('+1 week');
            $result[$actual->format('Y-m-d')] = "new Date({$actual->format('Y,n-1,j')})";
        }

        return $result;
    }
}
