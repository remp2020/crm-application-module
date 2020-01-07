<?php

namespace Crm\ApplicationModule\Hermes;

class LogFilter
{
    public static function add($filters)
    {
        return function ($record) use ($filters) {
            foreach ($filters as $filter) {
                if (isset($record['context']['payload'][$filter])) {
                    $record['context']['payload'][$filter] = '******';
                }
            }
            return $record;
        };
    }
}
