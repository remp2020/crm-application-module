<?php

namespace Crm\ApplicationModule;

class EnvironmentConfig
{
    public function get($key)
    {
        $val = Core::env($key);
        if ($val === null || $val === '') {
            return null;
        }
        return $val;
    }

    public function getDsn()
    {
        $port = Core::env('CRM_DB_PORT');
        if (!$port) {
            $port = 3306;
        }
        return Core::env('CRM_DB_ADAPTER') .
            ':host=' . Core::env('CRM_DB_HOST') .
            ';dbname=' . Core::env('CRM_DB_NAME') .
            ';port=' . $port;
    }
}
