<?php

namespace Crm\ApplicationModule;

use Monolog\LogRecord;

class LogRedact
{
    public static function add($filters)
    {
        return function (LogRecord $record) use ($filters) {
            $context = $record->context;
            foreach ($filters as $filter) {
                if (isset($context['payload'][$filter])) {
                    $context['payload'][$filter] = '******';
                }
            }
            return $record->with(context: $context);
        };
    }
}
