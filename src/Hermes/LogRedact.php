<?php

namespace Crm\ApplicationModule\Hermes;

use Closure;
use Monolog\LogRecord;

class LogRedact
{
    /**
     * @param array $input
     * @param string[] $fields
     * @return array
     */
    public static function redactArray(array $input, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $input[$field] = '******';
            }
        }
        return $input;
    }

    /**
     * @param string[] $filters
     * @return Closure
     */
    public static function add(array $filters): Closure
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
